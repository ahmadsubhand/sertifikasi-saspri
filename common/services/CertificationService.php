<?php

namespace common\services;

use common\enums\CertificationStatus;
use common\enums\IndicatorScoreAttribute;
use common\enums\TeamRole;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\form\AddMembersForm;
use common\models\form\CertificationListForm;
use common\models\form\ChangeMemberRoleForm;
use common\models\form\ExternalReviewForm;
use common\models\form\PeerReviewForm;
use common\models\form\RejectCertificationForm;
use common\models\form\SelfReviewForm;
use common\models\PeerTeamMember;
use common\models\SelfTeamMember;
use common\models\User;
use common\services\NotificationService;
use Yii;
use yii\helpers\Url;
use yii\web\BadRequestHttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class CertificationService
{
    public static function list(CertificationListForm $data)
    {
        $query = Certification::find()
            ->distinct()
            ->joinWith([
                'saspriK.district.regency.province'
            ]);

        if ($data->province_id) {
            $query->andWhere(['province.id' => $data->province_id]);
        }

        if ($data->regency_id) {
            $query->andWhere(['regency.id' => $data->regency_id]);
        }

        if ($data->district_id) {
            $query->andWhere(['district.id' => $data->district_id]);
        }

        if ($data->status !== $data::ALL) {
            $query->andWhere(
                $data->status === $data::ONGOING ? [
                    'not in',
                    'status',
                    [
                        CertificationStatus::PENDING_SELF_TEAM_FORMATION,
                        CertificationStatus::COMPLETED,
                    ]
                ] : [
                    'status' => CertificationStatus::COMPLETED,
                ]
            );
        }

        if ($data->level !== $data::ALL) {
            $query->andWhere(['level' => $data->level]);
        }

        $certs = (clone $query)
            ->orderBy([
                'updated_at' => SORT_DESC
            ])
            ->limit($data->limit + 1)
            ->offset($data->offset)
            ->all();
        $has_next = count($certs) > $data->limit;
        if ($has_next) {
            array_pop($certs);
        }

        return [
            'certifications' => $certs,
            'prev_link' => $data->offset > 0 ? Url::current(['offset' => max(0, $data->offset - $data->limit)]) : null,
            'next_link' => $has_next ? Url::current(['offset' => $data->offset + $data->limit]) : null,
            'offset' => $data->offset,
        ];
    }

    public static function findOrFail(int $id)
    {
        $certification = Certification::findOne($id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        return $certification;
    }

    public static function findSelfTeamMember(int $certification_id, int $user_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->where([
                'user_id' => $user_id,
                'certification_id' => $certification_id,
            ])
            ->joinWith('user')
            ->one();
        if (!$member) {
            throw new NotFoundHttpException('Anggota tidak ditemukan atau bukan anggota Tim Mandiri ini');
        }
        return $member;
    }

    public static function findPeerTeamMember(int $certification_id, int $user_id): PeerTeamMember
    {
        $member = PeerTeamMember::find()
            ->where([
                'user_id' => $user_id,
                'certification_id' => $certification_id,
            ])
            ->joinWith('user')
            ->one();
        if (!$member) {
            throw new NotFoundHttpException('Anggota tidak ditemukan atau bukan anggota Tim Sebaya ini');
        }
        return $member;
    }

    public static function addSelfTeamMembers(AddMembersForm $data)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);

        $valid_users = User::find()->availableForSelfTeam($saspri_k, $certification)
            ->andWhere(['id' => $data->user_ids])
            ->select('username')
            ->column();

        if (count($valid_users) !== count($data->user_ids)) {
            throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Mandiri saat ini');
        }

        $certification->save(); // untuk mendapatkan id jika sertifikasi baru diajukan
        $member_ids = $certification->addSelfTeamMembers($data->user_ids);

        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->self_team_due_date));

        foreach ($data->user_ids as $user_id) {
            NotificationService::send(
                $user_id,
                'Undangan bergabung Tim Mandiri',
                "Anda telah diundang untuk bergabung ke dalam Tim Mandiri di SASPRI-K kawasan {$district_name}. Batas akhir penerimaan undangan adalah {$formatted_due_date}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'tim-mandiri/detail?case_id=' . $certification->id,
                    'api_link' => '/self-team-member?self_team_member_id=' . $member_ids[$user_id],
                    'channels' => ['db', 'fcm'],
                ]
            );
        }

        return $valid_users;
    }

    public static function removeSelfTeamMember(int $user_id)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);
        $member = CertificationService::findSelfTeamMember($certification->id, $user_id);

        $district_name = $saspri_k->district->name ?? 'Kawasan';

        NotificationService::send(
            $user_id,
            'Pengeluaran dari Tim Mandiri',
            "Anda telah dikeluarkan dari Tim Mandiri di SASPRI-K kawasan {$district_name}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'tim-mandiri/index',
                'api_link' => null,
                'channels' => ['db', 'fcm'],
            ]
        );

        $member->delete();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::basicSelect())->one(),
        ];
    }

    public static function changeSelfTeamMemberRole(int $user_id, ChangeMemberRoleForm $data)
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->findOrCreateOnGoingCertification()
            ->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION);
        $member = CertificationService::findSelfTeamMember($certification->id, $user_id);
        $member->changeRole($data->role)->save();

        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $role_name = TeamRole::list()[$data->role] ?? $data->role;

        NotificationService::send(
            $user_id,
            'Perubahan Peran Tim Mandiri',
            "Peran Anda di Tim Mandiri pada SASPRI-K kawasan {$district_name} telah diubah menjadi {$role_name}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'tim-mandiri/detail?case_id=' . $certification->id,
                'api_link' => '/self-team-member?self_team_member_id=' . $member->id,
                'channels' => ['db', 'fcm'],
            ]
        );

        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::basicSelect())->one(),
        ];
    }

    public static function submitForSelfReview()
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->onGoingCertification;
        if (!$certification) {
            throw new NotFoundHttpException('Tidak ada sertifikasi yang sedang berlangsung');
        }
        $certification->validateCertificationStatus(CertificationStatus::PENDING_SELF_TEAM_FORMATION)
            ->validateApprovedSelfTeamComposition()
            ->submitForSelfReview()
            ->save();

        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->self_review_due_date));

        foreach ($certification->selfTeamMembers as $member) {
            NotificationService::send(
                $member->user_id,
                'Mulai ' . CertificationStatus::list()[$certification->status],
                "Proses " .  strtolower(CertificationStatus::list()[$certification->status]) .
                " di SASPRI-K kawasan {$district_name} telah dimulai. Batas akhir penilaian adalah {$formatted_due_date}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'tim-mandiri/detail?case_id=' . $certification->id,
                    'api_link' => null,
                    'channels' => ['db', 'fcm'],
                ]
            );
        }

        $coordinator_id = $saspri_k->coordinator_id;
        NotificationService::send(
            $coordinator_id,
            CertificationStatus::list()[CertificationStatus::SELF_REVIEW],
            "Sertifikasi SASPRI-K kawasan {$district_name} sedang dalam proses " .
            strtolower(CertificationStatus::list()[CertificationStatus::SELF_REVIEW]) . ". Batas akhir penilaian adalah {$formatted_due_date}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'saspri-k/pengajuan-sertifikasi',
                'api_link' => null,
                'channels' => ['db', 'fcm'],
            ]
        );

        return $certification;
    }

    public static function selfReview(int $certification_id, int $page = 1)
    {
        $member = CertificationService::findSelfTeamMember($certification_id, Yii::$app->user->id)
                ->checkSelfReviewPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::SELF_REVIEW);
        [
            'root_groups' => $root_groups,
            'current_root_group' => $current_root_group,
            'current_child_groups' => $current_child_groups
        ] = $certification->getAllIndicators($page);

        return [
            'is_leader' => $member->role === TeamRole::LEADER,
            'saspri_k' => $certification->saspriK,
            'certification' => $certification,
            'current_root_group' => $current_root_group,
            'current_child_groups' => $current_child_groups,
            'page' => $page,
            'total_pages' => count($root_groups),
        ];
    }

    public static function saveSelfReview(int $certification_id, SelfReviewForm $data)
    {
        CertificationService::findSelfTeamMember($certification_id, Yii::$app->user->id)
            ->checkSelfReviewPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::SELF_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW);

        return $certification->indicatorScores;
    }

    public static function finalizeSelfReview(int $certification_id, SelfReviewForm $data)
    {
        CertificationService::findSelfTeamMember($certification_id, Yii::$app->user->id)
            ->checkSelfReviewPermission()
            ->checkFinalizationPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::SELF_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::SELF_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::SELF_REVIEW)
            ->submitSelfReview();
        $certification->save();

        $saspri_k = $certification->saspriK;
        $coordinator_id = $saspri_k->coordinator_id;
        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->peer_team_due_date));

        NotificationService::send(
            $coordinator_id,
            CertificationStatus::list()[$certification->status],
            "Sertifikasi SASPRI-K kawasan {$district_name} sedang dalam proses " .
            strtolower(CertificationStatus::list()[$certification->status]) . ". Batas akhir pembentukan tim adalah {$formatted_due_date}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'saspri-k/pengajuan-sertifikasi',
                'api_link' => null,
                'channels' => ['db', 'fcm'],
            ]
        );

        return $certification;
    }

    public static function addPeerTeamMembers(int $certification_id, AddMembersForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);

        $valid_users = User::find()->availableForPeerTeam($certification)
            ->andWhere(['id' => $data->user_ids])
            ->select('username')
            ->column();

        if (count($valid_users) !== count($data->user_ids)) {
            throw new BadRequestHttpException('Beberapa user tidak valid atau sudah terdaftar di Tim Sebaya saat ini');
        }

        $member_ids = $certification->addPeerTeamMembers($data->user_ids);

        $district_name = $certification->saspriK->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->peer_team_due_date));

        foreach ($data->user_ids as $user_id) {
            NotificationService::send(
                $user_id,
                'Undangan bergabung Tim Sebaya',
                "Anda telah diundang untuk bergabung ke dalam Tim Sebaya di SASPRI-K kawasan {$district_name}. Batas akhir penerimaan undangan adalah {$formatted_due_date}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'tim-sebaya/detail?case_id=' . $certification->id,
                    'api_link' => '/peer-team-member?peer_team_member_id=' . $member_ids[$user_id],
                    'channels' => ['db', 'fcm'],
                ]
            );
        }

        return $valid_users;
    }

    public static function removePeerTeamMember(int $certification_id, int $user_id)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);
        $member = CertificationService::findPeerTeamMember($certification->id, $user_id);

        $district_name = $certification->saspriK->district->name ?? 'Kawasan';

        NotificationService::send(
            $user_id,
            'Pengeluaran dari Tim Sebaya',
            "Anda telah dikeluarkan dari Tim Sebaya di SASPRI-K kawasan {$district_name}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'tim-sebaya/index',
                'api_link' => null,
                'channels' => ['db', 'fcm'],
            ]
        );

        $member->delete();
        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::basicSelect())->one(),
        ];
    }

    public static function changePeerTeamMemberRole(int $certification_id, int $user_id, ChangeMemberRoleForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION);
        $member = CertificationService::findPeerTeamMember($certification->id, $user_id);
        $member->changeRole($data->role)->save();

        $district_name = $certification->saspriK->district->name ?? 'Kawasan';
        $role_name = TeamRole::list()[$data->role] ?? $data->role;

        NotificationService::send(
            $user_id,
            'Perubahan Peran Tim Sebaya',
            "Peran Anda di Tim Sebaya pada SASPRI-K kawasan {$district_name} telah diubah menjadi {$role_name}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'tim-sebaya/detail?case_id=' . $certification->id,
                'api_link' => 'peer-team-member?peer_team_member_id=' . $member->id,
                'channels' => ['db', 'fcm'],
            ]
        );

        return [
            ...$member,
            'user' => $member->getUser()->select(UserHelper::basicSelect())->one(),
        ];
    }

    public static function submitForPeerReview(int $certification_id)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $certification->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION)
            ->validateApprovedPeerTeamComposition()
            ->submitForPeerReview()
            ->save();
        $saspri_k = $certification->saspriK;

        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->peer_review_due_date));

        foreach ($certification->peerTeamMembers as $member) {
            NotificationService::send(
                $member->user_id,
                'Mulai ' . CertificationStatus::list()[$certification->status],
                "Proses " .  strtolower(CertificationStatus::list()[$certification->status]) .
                " di SASPRI-K kawasan {$district_name} telah dimulai. Batas akhir penilaian adalah {$formatted_due_date}.",
                [
                    'sender_id' => Yii::$app->user->id,
                    'web_link' => 'tim-sebaya/detail?case_id=' . $certification->id,
                    'api_link' => null,
                    'channels' => ['db', 'fcm'],
                ]
            );
        }

        $coordinator_id = $saspri_k->coordinator_id;
        NotificationService::send(
            $coordinator_id,
            CertificationStatus::list()[$certification->status],
            "Sertifikasi SASPRI-K kawasan {$district_name} sedang dalam proses " .
            strtolower(CertificationStatus::list()[$certification->status]) . ". Batas akhir penilaian adalah {$formatted_due_date}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'saspri-k/pengajuan-sertifikasi',
                'api_link' => null,
                'channels' => ['db', 'fcm'],
            ]
        );

        return $certification;
    }

    public static function peerReview(int $certification_id, int $page)
    {
        $member = CertificationService::findPeerTeamMember($certification_id, Yii::$app->user->id)
            ->checkPeerReviewPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PEER_REVIEW);
        [
            'root_groups' => $root_groups,
            'current_root_group' => $current_root_group,
            'current_child_groups' => $current_child_groups
        ] = $certification->getAllIndicators($page);

        return [
            'is_leader' => $member->role === TeamRole::LEADER,
            'saspri_k' => $certification->saspriK,
            'certification' => $certification,
            'current_root_group' => $current_root_group,
            'current_child_groups' => $current_child_groups,
            'page' => $page,
            'total_pages' => count($root_groups),
        ];
    }

    public static function savePeerReview(int $certification_id, PeerReviewForm $data)
    {
        CertificationService::findPeerTeamMember($certification_id, Yii::$app->user->id)
            ->checkPeerReviewPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PEER_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::PEER_REVIEW);

        return $certification->indicatorScores;
    }

    public static function finalizePeerReview(int $certification_id, PeerReviewForm $data)
    {
        CertificationService::findPeerTeamMember($certification_id, Yii::$app->user->id)
            ->checkPeerReviewPermission()
            ->checkFinalizationPermission();

        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::PEER_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::PEER_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::PEER_REVIEW)
            ->calculateTotalScore(IndicatorScoreAttribute::PEER_REVIEW)
            ->setGrade()
            ->submitPeerReview();
        $certification->save();

        $saspri_k = $certification->saspriK;
        $coordinator_id = $saspri_k->coordinator_id;
        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->external_review_due_date));

        NotificationService::send(
            $coordinator_id,
            CertificationStatus::list()[$certification->status],
            "Sertifikasi SASPRI-K kawasan {$district_name} sedang dalam proses " .
            strtolower(CertificationStatus::list()[$certification->status]) . ". Batas akhir external review adalah {$formatted_due_date}.",
            [
                'sender_id' => Yii::$app->user->id,
                'web_link' => 'saspri-k/pengajuan-sertifikasi',
                'api_link' => null,
                'channels' => ['db', 'fcm'],
            ]
        );

        return $certification;
    }

    public static function externalReview(int $certification_id, int $page)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW);

        [
            'root_groups' => $root_groups,
            'current_root_group' => $current_root_group,
            'current_child_groups' => $current_child_groups
        ] = $certification->getAllIndicators($page);

        return [
            'saspri_k' => $certification->saspriK,
            'certification' => $certification,
            'current_root_group' => $current_root_group,
            'current_child_groups' => $current_child_groups,
            'page' => $page,
            'total_pages' => count($root_groups),
        ];
    }

    public static function saveExternalReview(int $certification_id, ExternalReviewForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->saveScores($data->indicator_scores, IndicatorScoreAttribute::EXTERNAL_REVIEW);

        return $certification->indicatorScores;
    }

    public static function transcripts(int $certification_id)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->calculateTotalScore(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->setGrade();
        $certification->issued_at = date('Y-m-d H:i:s');
        $certification->calculateNextCertificationDueDate();
        $transcripts = $certification->getTranscripts();
        return [
            'certification' => $certification,
            'transcripts' => $transcripts,
        ];
    }

    public static function finalizeExternalReview(int $certification_id)
    {
        $certification = CertificationService::findOrFail($certification_id)
            ->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->ensureAllScoresFilled(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->calculateTotalScore(IndicatorScoreAttribute::EXTERNAL_REVIEW)
            ->setGrade()
            ->submitExternalReview()
            ->generateCertificationCode()
            ->calculateNextCertificationDueDate();
        $certification->save();

        $saspri_k = $certification->saspriK;
        $coordinator_id = $saspri_k->coordinator_id;
        $recipients = [
            [
                'user_ids' => [$coordinator_id],
                'web_link' => 'saspri-k/detail?case_id=' . $certification->id,
            ],
            [
                'user_ids' => $certification->getSelfTeamMembers()->select('user_id')->column(),
                'web_link' => 'tim-mandiri/detail?case_id=' . $certification->id,
            ],
            [
                'user_ids' => $certification->getPeerTeamMembers()->select('user_id')->column(),
                'web_link' => 'tim-sebaya/detail?case_id=' . $certification->id,
            ],
        ];


        $district_name = $saspri_k->district->name ?? 'Kawasan';
        foreach ($recipients as $recipient) {
            foreach ($recipient['user_ids'] as $user_id) {
                NotificationService::send(
                    $user_id,
                    'Sertifikasi Selesai',
                    "Sertifikasi SASPRI-K kawasan {$district_name} telah selesai.",
                    [
                        'sender_id' => Yii::$app->user->id,
                        'web_link' => $recipient['web_link'],
                        'api_link' => null,
                        'channels' => ['db', 'fcm'],
                    ]
                );
            }
        }

        return $certification;
    }

    public static function rejectPeerTeamFormationRequest(int $certification_id, RejectCertificationForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $certification->validateCertificationStatus(CertificationStatus::PENDING_PEER_TEAM_FORMATION)
            ->reject($data->rejection_reason, CertificationStatus::SELF_REVIEW)
            ->save();

        $saspri_k = $certification->saspriK;
        $coordinator_id = $saspri_k->coordinator_id;
        $recipients = [
            [
                'user_ids' => [$coordinator_id],
                'web_link' => 'saspri-k/pengajuan-sertifikasi',
                'api_link' => '/certification?certification_id=' . $certification->id,
            ],
            [
                'user_ids' => $certification->getSelfTeamMembers()->select('user_id')->column(),
                'web_link' => 'tim-mandiri/detail?case_id=' . $certification->id,
                'api_link' => '/certification?certification_id=' . $certification->id,
            ],
        ];

        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->self_review_due_date));

        foreach ($recipients as $recipient) {
            foreach ($recipient['user_ids'] as $user_id) {
                NotificationService::send(
                    $user_id,
                    'Sertifikasi Ditolak',
                    "Sertifikasi SASPRI-K kawasan {$district_name} ditolak oleh SASPRI-N dan dikembalikan ke proses " .
                    strtolower(CertificationStatus::list()[$certification->status]) . ". Batas akhir penilaian adalah {$formatted_due_date}.",
                    [
                        'sender_id' => Yii::$app->user->id,
                        'web_link' => $recipient['web_link'],
                        'api_link' => $recipient['api_link'],
                        'channels' => ['db', 'fcm'],
                    ]
                );
            }
        }

        return $certification;
    }

    public static function rejectExternalReviewRequest(int $certification_id, RejectCertificationForm $data)
    {
        $certification = CertificationService::findOrFail($certification_id);
        $certification->validateCertificationStatus(CertificationStatus::EXTERNAL_REVIEW)
            ->reject($data->rejection_reason, CertificationStatus::PEER_REVIEW)
            ->save();

        $saspri_k = $certification->saspriK;
        $coordinator_id = $saspri_k->coordinator_id;
        $recipients = [
            [
                'user_ids' => [$coordinator_id],
                'web_link' => 'saspri-k/pengajuan-sertifikasi',
                'api_link' => '/certification?certification_id=' . $certification->id,
            ],
            [
                'user_ids' => $certification->getPeerTeamMembers()->select('user_id')->column(),
                'web_link' => 'tim-sebaya/detail?case_id=' . $certification->id,
                'api_link' => '/certification?certification_id=' . $certification->id,
            ],
        ];

        $district_name = $saspri_k->district->name ?? 'Kawasan';
        $formatted_due_date = date('d-m-Y H:i', strtotime($certification->peer_review_due_date));

        foreach ($recipients as $recipient) {
            foreach ($recipient['user_ids'] as $user_id) {
                NotificationService::send(
                    $user_id,
                    'Sertifikasi Ditolak',
                    "Sertifikasi SASPRI-K kawasan {$district_name} ditolak oleh SASPRI-N dan dikembalikan ke proses " .
                    strtolower(CertificationStatus::list()[$certification->status]) . ". Batas akhir penilaian adalah {$formatted_due_date}.",
                    [
                        'sender_id' => Yii::$app->user->id,
                        'web_link' => $recipient['web_link'],
                        'api_link' => $recipient['api_link'],
                        'channels' => ['db', 'fcm'],
                    ]
                );
            }
        }

        return $certification;
    }

    public static function cancel()
    {
        $saspri_k = UserService::findSaspriKAsCoordinatorOrFail();
        $certification = $saspri_k->onGoingCertification;

        if (!$certification) {
            throw new NotFoundHttpException('Tidak ada sertifikasi yang sedang berlangsung');
        }
        if (!$certification->is_rejected) {
            throw new UnprocessableEntityHttpException(
                'Anda hanya boleh membatalkan sertifikasi yang sudah ditolak oleh Admin SASPRI-N'
            );
        }

        $recipients = [
            [
                'user_ids' => $certification->getSelfTeamMembers()->select('user_id')->column(),
                'web_link' => 'tim-mandiri/index',
            ],
            [
                'user_ids' => $certification->getPeerTeamMembers()->select('user_id')->column(),
                'web_link' => 'tim-sebaya/index',
            ],
        ];

        $district_name = $saspri_k->district->name ?? 'Kawasan';
        foreach ($recipients as $recipient) {
            foreach ($recipient['user_ids'] as $user_id) {
                NotificationService::send(
                    $user_id,
                    'Sertifikasi Dibatalkan',
                    "Sertifikasi SASPRI-K kawasan {$district_name} dibatalkan oleh Wali",
                    [
                        'sender_id' => Yii::$app->user->id,
                        'web_link' => $recipient['web_link'],
                        'api_link' => null,
                        'channels' => ['db', 'fcm'],
                    ]
                );
            }
        }

        $certification->delete();

        return $certification;
    }
}
