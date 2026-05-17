<?php

namespace frontend\controllers;

use common\enums\ApprovalStatus;
use common\enums\CertificationStatus;
use common\enums\UserRole;
use common\enums\TeamRole;
use common\helpers\TeamHelper;
use common\helpers\UserHelper;
use common\models\Certification;
use common\models\SelfTeamMember;
use Exception;
use Yii;
use yii\db\ActiveQuery;
use yii\filters\AccessControl;
use yii\filters\VerbFilter;
use yii\web\BadRequestHttpException;
use yii\web\Controller;
use yii\web\ForbiddenHttpException;
use yii\web\HttpException;
use yii\web\NotFoundHttpException;
use yii\web\UnprocessableEntityHttpException;

class TimMandiriController extends Controller
{
    public function behaviors()
    {
        return [
            'access' => [
                'class' => AccessControl::class,
                'rules' => [
                    [
                        'allow' => true,
                        'roles' => [UserRole::USER],
                    ]
                ]
            ],
            'verbs' => [
                'class' => VerbFilter::class,
                'actions' => [
                    'setuju' => ['post'],
                    'tolak' => ['post'],
                    'simpan-sementara-self-review' => ['post'],
                    'finalisasi-self-review' => ['post'],
                ],
            ],
        ];
    }

    public function actionIndex()
    {
        $base_query = SelfTeamMember::find()
            ->joinWith('certification c')
            ->where(['user_id' => Yii::$app->user->id])
            ->with('certification.saspriK');

        $self_team_member_request = (clone $base_query)
            ->andWhere(['c.status' => CertificationStatus::PENDING_SELF_TEAM_FORMATION])
            ->orderBy(['c.self_team_due_date' => SORT_ASC])
            ->all();

        $self_team_member_uncompleted = (clone $base_query)
            ->andWhere([
                'not in',
                'c.status',
                [
                    CertificationStatus::PENDING_SELF_TEAM_FORMATION,
                    CertificationStatus::COMPLETED,
                ]
            ])
            ->orderBy(['c.updated_at' => SORT_DESC])
            ->all();

        $self_team_member_completed = (clone $base_query)
            ->andWhere(['c.status' => CertificationStatus::COMPLETED])
            ->orderBy(['c.issued_at' => SORT_DESC])
            ->all();

        return $this->render('index', [
            'self_team_member_request' => $self_team_member_request,
            'self_team_member_uncompleted' => $self_team_member_uncompleted,
            'self_team_member_completed' => $self_team_member_completed,
        ]);
    }

    public function actionSetuju(int $self_team_member_id)
    {
        try {
            $member = $this->findPendingSelfTeamMemberOrFail($self_team_member_id);
            $member->approveRequest()->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menyetujui permintaan bergabung Tim Mandiri');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionTolak(int $self_team_member_id)
    {
        try {
            $member = $this->findPendingSelfTeamMemberOrFail($self_team_member_id);
            $member->rejectRequest()->save(false);

            Yii::$app->session->setFlash('success', 'Berhasil menolak permintaan bergabung Tim Mandiri');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionSelfReview(int $certification_id, int $page = 1)
    {
        try {
            $member = $this->checkSelfReviewPermission($certification_id);
            $certification = $this->findCertificationOrFail($certification_id);
            [
                'root_groups' => $root_groups,
                'current_root_group' => $current_root_group,
                'current_child_groups' => $current_child_groups
            ] = TeamHelper::getAllIndicators($certification, $page);

            return $this->render('selfReview', [
                'is_leader' => $member->role === TeamRole::LEADER,
                'saspri_k' => $certification->saspriK,
                'certification' => $certification,
                'current_root_group' => $current_root_group,
                'current_child_groups' => $current_child_groups,
                'page' => $page,
                'total_pages' => count($root_groups),
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof ForbiddenHttpException ||
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException ||
                    $error instanceof BadRequestHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionSimpanSementaraSelfReview(int $certification_id, int $page = 1)
    {
        try {
            $this->checkSelfReviewPermission($certification_id);
            $this->findCertificationOrFail($certification_id)
                ->saveSelfReviewScores(Yii::$app->request->post('indicator_scores', []));

            Yii::$app->session->setFlash('success', 'Perubahan berhasil disimpan sementara');
            $targetPage = Yii::$app->request->post('target_page', $page);
            return $this->redirect([
                'self-review',
                'certification_id' => $certification_id,
                'page' => $targetPage,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if ($error instanceof BadRequestHttpException) {
                    return $this->redirect([
                        'self-review',
                        'certification_id' => $certification_id,
                        'page' => $page
                    ]);
                } elseif (
                    $error instanceof ForbiddenHttpException ||
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionFinalisasiSelfReview(int $certification_id)
    {
        try {
            $member = $this->checkSelfReviewPermission($certification_id);
            TeamHelper::isMemberALeader($member);

            $this->findCertificationOrFail($certification_id)
                ->saveSelfReviewScores(Yii::$app->request->post('indicator_scores'))
                ->submitSelfReview()
                ->save(false);

            Yii::$app->session->setFlash('success', 'Self Review berhasil difinalisasi');
            return $this->redirect(['index']);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof BadRequestHttpException ||
                    (
                        $error instanceof UnprocessableEntityHttpException &&
                        str_contains($error->getMessage(), 'ketua')
                    )
                ) {
                    return $this->redirect([
                        'self-review',
                        'certification_id' => $certification_id,
                        'page' => 1
                    ]);
                } elseif (
                    $error instanceof ForbiddenHttpException ||
                    $error instanceof NotFoundHttpException ||
                    $error instanceof UnprocessableEntityHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    public function actionDetail(int $case_id)
    {
        try {
            $cert = Certification::findOne(['id' => $case_id]);
            if ($cert->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
                $this->checkSelfReviewPermission($case_id);
            } else {
                $member = SelfTeamMember::find()
                    ->where([
                        'certification_id' => $case_id,
                        'user_id' => Yii::$app->user->id,
                    ])
                    ->exists();
                if (!$member) {
                    throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Mandiri');
                }
            }
            $saspri_k = $cert->saspriK;
            $self_team = $cert->getSelfTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
            $peer_team = $cert->getPeerTeamMembers()
                ->with([
                    'user' => function (ActiveQuery $query) {
                        $query->select(UserHelper::$basicSelect);
                    },
                ])
                ->all();
            return $this->render('detail', [
                'id' => $case_id,
                'saspri' => $saspri_k,
                'cert' => $cert,
                'self_team' => $self_team,
                'peer_team' => $peer_team,
            ]);
        } catch (Exception $error) {
            if ($error instanceof HttpException) {
                Yii::$app->session->setFlash('error', $error->getMessage());
                if (
                    $error instanceof ForbiddenHttpException ||
                    $error instanceof NotFoundHttpException
                ) {
                    return $this->redirect(['index']);
                }
            }
            throw $error;
        }
    }

    private function findPendingSelfTeamMemberOrFail(int $self_team_member_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->joinWith('certification')
            ->where([
                'self_team_members.id' => $self_team_member_id,
                'self_team_members.user_id' => Yii::$app->user->id,
            ])
            ->one();

        if (!$member) {
            throw new NotFoundHttpException('Data tidak ditemukan atau Anda bukan anggota Tim Mandiri ini');
        }
        if ($member->certification->status !== CertificationStatus::PENDING_SELF_TEAM_FORMATION) {
            throw new UnprocessableEntityHttpException('Permintaan sudah tidak dapat diubah karena status sertifikasi sudah berjalan');
        }
        if ($member->status !== ApprovalStatus::PENDING) {
            throw new UnprocessableEntityHttpException('Permintaan ini sudah direspon sebelumnya');
        }

        return $member;
    }

    private function checkSelfReviewPermission(int $certification_id): SelfTeamMember
    {
        $member = SelfTeamMember::find()
            ->where([
                'certification_id' => $certification_id,
                'user_id' => Yii::$app->user->id,
                'status' => ApprovalStatus::APPROVED,
            ])
            ->one();
        if (!$member) {
            throw new ForbiddenHttpException('Akses ditolak karena Anda bukan anggota dari Tim Mandiri');
        }
        return $member;
    }

    protected function findCertificationOrFail(int $certification_id): Certification
    {
        $certification = Certification::findOne($certification_id);
        if (!$certification) {
            throw new NotFoundHttpException('Sertifikasi tidak ditemukan');
        }
        if ($certification->status !== CertificationStatus::SELF_REVIEW) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . CertificationStatus::list()[CertificationStatus::SELF_REVIEW]
            );
        }
        return $certification;
    }
}
