<?php

namespace common\models;

use common\enums\ApprovalStatus;
use common\enums\CertificateGrade;
use common\enums\CertificationStatus;
use common\enums\IndicatorScoreAttribute;
use common\enums\TeamRole;
use common\helpers\UserHelper;
use Exception;
use yii\behaviors\TimestampBehavior;
use yii\web\BadRequestHttpException;
use yii\web\ConflictHttpException;
use yii\web\UnprocessableEntityHttpException;

/**
 * This is the model class for table "certification".
 *
 * @property int $id
 * @property int $saspri_k_id
 * @property int $created_at
 * @property int $updated_at
 * @property string $purpose
 * @property string $status
 * @property string|null $self_team_due_date
 * @property string|null $self_review_due_date
 * @property string|null $peer_team_due_date
 * @property string|null $peer_review_due_date
 * @property string|null $external_review_due_date
 * @property string $level
 * @property string $code
 * @property string|null $issued_at
 * @property int|null $total_score
 * @property string|null $grade
 * @property string|null $next_certification_due_date
 * @property int|null $is_rejected
 * @property string|null $rejection_reason
 * @property int $assessment_id
 *
 * @property Assessment $assessment
 * @property IndicatorScore[] $indicatorScores
 * @property Indicator[] $indicators
 * @property PeerTeamMember[] $peerTeamMembers
 * @property PeerTeamMember[] $fullPeerTeamMembers
 * @property SaspriK $saspriK
 * @property SelfTeamMember[] $selfTeamMembers
 * @property SelfTeamMember[] $fullSelfTeamMembers
 * @property User[] $users
 * @property User[] $users0
 */
class Certification extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'certification';
    }

    /**
     * {@inheritdoc}
     */
    public function behaviors()
    {
        return [
            TimestampBehavior::class,
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['saspri_k_id', 'purpose', 'level', 'assessment_id'], 'required'],
            [['saspri_k_id', 'total_score', 'is_rejected', 'assessment_id', 'created_at', 'updated_at'], 'integer'],
            [['self_team_due_date', 'self_review_due_date', 'peer_team_due_date', 'peer_review_due_date', 'external_review_due_date', 'issued_at', 'next_certification_due_date'], 'safe'],
            [['purpose', 'status', 'level', 'code', 'grade', 'rejection_reason'], 'string', 'max' => 255],
            [['assessment_id'], 'exist', 'skipOnError' => true, 'targetClass' => Assessment::class, 'targetAttribute' => ['assessment_id' => 'id']],
            [['saspri_k_id'], 'exist', 'skipOnError' => true, 'targetClass' => SaspriK::class, 'targetAttribute' => ['saspri_k_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'saspri_k_id' => 'Saspri K ID',
            'created_at' => 'Created At',
            'updated_at' => 'Updated At',
            'purpose' => 'Purpose',
            'status' => 'Status',
            'self_team_due_date' => 'Self Team Due Date',
            'self_review_due_date' => 'Self Review Due Date',
            'peer_team_due_date' => 'Peer Team Due Date',
            'peer_review_due_date' => 'Peer Review Due Date',
            'external_review_due_date' => 'External Review Due Date',
            'level' => 'Level',
            'code' => 'Code',
            'issued_at' => 'Issued At',
            'total_score' => 'Total Score',
            'grade' => 'Grade',
            'next_certification_due_date' => 'Next Certification Due Date',
            'is_rejected' => 'Is Rejected',
            'rejection_reason' => 'Rejection Reason',
            'assessment_id' => 'Assessment ID',
        ];
    }

    /**
     * Gets query for [[Assessment]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getAssessment()
    {
        return $this->hasOne(Assessment::class, ['id' => 'assessment_id']);
    }

    /**
     * Gets query for [[IndicatorScores]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicatorScores()
    {
        return $this->hasMany(IndicatorScore::class, ['certification_id' => 'id']);
    }

    /**
     * Gets query for [[Indicators]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getIndicators()
    {
        return $this->hasMany(Indicator::class, ['id' => 'indicator_id'])->viaTable('indicator_score', ['certification_id' => 'id']);
    }

    /**
     * Gets query for [[PeerTeamMembers]] - Approved only
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeerTeamMembers()
    {
        return $this->hasMany(PeerTeamMember::class, ['certification_id' => 'id'])
            ->andWhere(['status' => ApprovalStatus::APPROVED]);
    }

    /**
     * Gets query for [[PeerTeamMembers]] - All status
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFullPeerTeamMembers()
    {
        return $this->hasMany(PeerTeamMember::class, ['certification_id' => 'id']);
    }

    /**
     * Gets query for [[SaspriK]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSaspriK()
    {
        return $this->hasOne(SaspriK::class, ['id' => 'saspri_k_id']);
    }

    /**
     * Gets query for [[SelfTeamMembers]] - Approved only
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSelfTeamMembers()
    {
        return $this->hasMany(SelfTeamMember::class, ['certification_id' => 'id'])
            ->andWhere(['status' => ApprovalStatus::APPROVED]);
    }

    /**
     * Gets query for [[SelfTeamMembers]] - All status
     *
     * @return \yii\db\ActiveQuery
     */
    public function getFullSelfTeamMembers()
    {
        return $this->hasMany(SelfTeamMember::class, ['certification_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('peer_team_member', ['certification_id' => 'id']);
    }

    /**
     * Gets query for [[Users0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers0()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('self_team_member', ['certification_id' => 'id']);
    }

    public function submitForSelfReview(): Certification
    {
        // Jika masih ada yang pending, maka otomatis menjadi rejected
        SelfTeamMember::updateAll(
            ['status' => ApprovalStatus::REJECTED],
            ['certification_id' => $this->id, 'status' => ApprovalStatus::PENDING]
        );

        $this->status = CertificationStatus::SELF_REVIEW;
        $this->self_team_due_date = date('Y-m-d H:i:s');
        $this->self_review_due_date = date('Y-m-d H:i:s', strtotime('+2 weeks'));
        return $this;
    }

    public function submitSelfReview(): Certification
    {
        $this->status = CertificationStatus::PENDING_PEER_TEAM_FORMATION;
        $this->self_review_due_date = date('Y-m-d H:i:s');
        $this->peer_team_due_date = date('Y-m-d H:i:s', strtotime('+1 weeks'));
        return $this;
    }

    public function submitForPeerReview(): Certification
    {
        // Jika masih ada yang pending, maka otomatis menjadi rejected
        PeerTeamMember::updateAll(
            ['status' => ApprovalStatus::REJECTED],
            ['certification_id' => $this->id, 'status' => ApprovalStatus::PENDING]
        );

        $this->status = CertificationStatus::PEER_REVIEW;
        $this->peer_team_due_date = date('Y-m-d H:i:s');
        $this->peer_review_due_date = date('Y-m-d H:i:s', strtotime('+2 weeks'));
        return $this;
    }

    public function submitPeerReview(): Certification
    {
        $this->status = CertificationStatus::EXTERNAL_REVIEW;
        $this->peer_review_due_date = date('Y-m-d H:i:s');
        $this->external_review_due_date = date('Y-m-d H:i:s', strtotime('+2 weeks'));
        return $this;
    }

    public function submitExternalReview(): Certification
    {
        $this->status = CertificationStatus::COMPLETED;
        $this->external_review_due_date = date('Y-m-d H:i:s');
        $this->issued_at = date('Y-m-d H:i:s');
        return $this;
    }

    public function addSelfTeamMembers(array $user_ids)
    {
        foreach ($user_ids as $user_id) {
            $already_exists = SelfTeamMember::find()
                ->where([
                    'id' => $user_id,
                    'certification_id' => $this->id,
                ])
                ->exists();
            if ($already_exists) {
                throw new ConflictHttpException('Anggota sudah pernah ditambahkan');
            }

            $member = new SelfTeamMember();
            $member->user_id = $user_id;
            $member->certification_id = $this->id;
            $member->status = ApprovalStatus::PENDING;
            $member->role = TeamRole::MEMBER;
            $member->save(false);
        }
    }

    public function addPeerTeamMembers(array $user_ids)
    {
        foreach ($user_ids as $user_id) {
            $already_exists = PeerTeamMember::find()
                ->where([
                    'id' => $user_id,
                    'certification_id' => $this->id,
                ])
                ->exists();
            if ($already_exists) {
                throw new ConflictHttpException('Anggota sudah pernah ditambahkan');
            }

            $member = new PeerTeamMember();
            $member->user_id = $user_id;
            $member->certification_id = $this->id;
            $member->status = ApprovalStatus::PENDING;

            if (UserHelper::isUserAnAdmin($user_id)) {
                $member->role = TeamRole::FACILITATOR;
            } else {
                $member->role = TeamRole::MEMBER;
            }

            $member->save(false);
        }
    }

    public function validateApprovedSelfTeamComposition()
    {
        /** @var SelfTeamMember[] $approved_members */
        $approved_members = $this->getSelfTeamMembers()->where(['status' => ApprovalStatus::APPROVED])->all();
        $approvedCount = count($approved_members);
        $leaderCount = count(array_filter($approved_members, fn ($m) => $m->role === TeamRole::LEADER));
        $memberCount = count(array_filter($approved_members, fn ($m) => $m->role !== TeamRole::LEADER));

        if ($approvedCount === 0 || $approvedCount % 3 !== 0) {
            throw new UnprocessableEntityHttpException('Jumlah anggota Tim Mandiri yang setuju bergabung harus kelipatan 3');
        }
        if ($leaderCount !== 1 || $memberCount < 2) {
            throw new UnprocessableEntityHttpException('Tim Mandiri harus terdiri dari 1 ketua dan minimal 2 anggota lainnya');
        }

        return $this;
    }

    public function validateApprovedPeerTeamComposition()
    {
        /** @var PeerTeamMember[] $approved_members */
        $approved_members = $this->getPeerTeamMembers()->where(['status' => ApprovalStatus::APPROVED])->all();

        $facilitatorCount = 0;
        $leaderCount = 0;
        $memberCount = 0;
        $saspriKIds = [];

        foreach ($approved_members as $member) {
            if ($member->role === TeamRole::FACILITATOR) {
                $facilitatorCount++;
            } else {
                $saspriKIds[] = $member->user->saspri_k_id;
                if ($member->role === TeamRole::LEADER) {
                    $leaderCount++;
                } elseif ($member->role === TeamRole::MEMBER) {
                    $memberCount++;
                }
            }
        }

        if ($facilitatorCount !== 1 || $leaderCount !== 1 || $memberCount < 1) {
            throw new UnprocessableEntityHttpException(
                'Anggota yang menyetujui bergabung di Tim sebaya harus terdiri dari minimal 2 orang ' .
                '(salah sartu bertindak sebagai ketua) dari SASPRI-K lainnya dan 1 pendamping dari SASPRI-N'
            );
        }

        // Validasi tidak dari SASPRI-K yang sama
        // if (count(array_unique($saspriKIds)) !== count($saspriKIds)) {
        //     throw new UnprocessableEntityHttpException(
        //         'Masing-masing anggota harus dari SASPRI-K yang berbeda satu sama lain'
        //     );
        // }

        return $this;
    }

    public function validateCertificationStatus(string $status) {
        if (!in_array($status, CertificationStatus::values())) {
            throw new Exception('Invalid certification status');
        }

        if ($this->status !== $status) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi tidak dalam tahap ' . strtolower(CertificationStatus::list()[$status])
            );
        }

        return $this;
    }

    public function saveScores(array $indicator_scores, string $score_attribute)
    {
        if (!in_array($score_attribute, IndicatorScoreAttribute::values())) {
            throw new Exception('Invalid attribute name of indicator score');
        }

        try {
            foreach ($indicator_scores as $indicator_id => $indicator_score) {
                $indicator_score_model = $this->findOrCreateIndicatorScore($indicator_id);
                $indicator_score_model->fillScore($indicator_score ?? [], $score_attribute);
                if ($score_attribute === IndicatorScoreAttribute::SELF_REVIEW) {
                    $indicator_score_model->handleEvidenceUpload($this->id);
                }
                if ($score_attribute === IndicatorScoreAttribute::PEER_REVIEW) {
                    $indicator_score_model->fillStatus($indicator_score['status'] ?? null);
                }
                $indicator_score_model->save();
            }

            return $this;
        } catch (Exception $error) {
            if ($error instanceof BadRequestHttpException) {
                throw new BadRequestHttpException($error->getMessage());
            }
            throw $error;
        }
    }

    public function ensureAllScoresFilled(string $score_attribute)
    {
        if (!in_array($score_attribute, IndicatorScoreAttribute::values())) {
            throw new Exception('Invalid attribute name of indicator score');
        }

        $existing_scores = $this->indicatorScores;
        $assessment_indicator_count = 0;
        foreach ($this->assessment->indicatorGroups as $indicator_group) {
            $assessment_indicator_count += $indicator_group->getIndicators()->count();
        }
        if ($assessment_indicator_count !== count($existing_scores)) {
            throw new BadRequestHttpException('Seluruh indikator wajib diisi sebelum finalisasi');
        }
        
        foreach ($existing_scores as $existing_score) {
            if (!$existing_score->$score_attribute) {
                throw new BadRequestHttpException(
                    'Seluruh indikator wajib diberikan penilaian sebelum finalisasi'
                );
            }

            if (
                $score_attribute === IndicatorScoreAttribute::PEER_REVIEW &&
                !$existing_score->status
            ) {
                throw new BadRequestHttpException(
                    'Seluruh status wajib diisi sebelum finalisasi'
                );
            }
        }

        return $this;
    }

    public function calculateTotalScore(string $score_attribute): self
    {
        if (!in_array($score_attribute, IndicatorScoreAttribute::values())) {
            throw new Exception('Invalid attribute name of indicator score');
        }

        $total_score = 0;
        $root_groups = $this->assessment->rootGroups;

        foreach ($root_groups as $root_group) {
            $group_total_weighted_sum = 0;
            $sub_groups = $root_group->childGroups;

            foreach ($sub_groups as $sub_group) {
                $sub_group_sum = 0;
                $indicator_count = count($sub_group->indicators);

                foreach ($sub_group->indicators as $indicator) {
                    /** @var IndicatorScore|null $score_model */
                    $score_model = $indicator->getIndicatorScores()->where(['certification_id' => $this->id])->one();
                    $sub_group_sum += $score_model ? ($score_model->$score_attribute ?? 0) : 0;
                }

                $sub_group_average = $indicator_count > 0 ? ($sub_group_sum / $indicator_count) : 0;
                $group_total_weighted_sum += $sub_group_average * ($sub_group->weight / 100);
            }
            $total_score += $group_total_weighted_sum * ($root_group->weight / 100);
        }

        $this->total_score = (int) round($total_score);
        return $this;
    }

    public function setGrade(): self
    {
        if ($this->total_score >= 90) {
            $this->grade = CertificateGrade::A;
        } elseif ($this->total_score >= 75) {
            $this->grade = CertificateGrade::AB;
        } elseif ($this->total_score >= 60) {
            $this->grade = CertificateGrade::B;
        } elseif ($this->total_score >= 50) {
            $this->grade = CertificateGrade::BC;
        } else {
            $this->grade = CertificateGrade::C;
        }
        return $this;
    }

    public function generateCertificationCode()
    {
        $year = date('Y', strtotime($this->issued_at));
        $districtCode = $this->saspriK->district->code ?? '0000';
        $levelCode = strtoupper($this->level);

        $this->code = sprintf('CERT/%s/%s/%s/%04d', $levelCode, $districtCode, $year, $this->id);
        return $this;
    }

    public function calculateNextCertificationDueDate()
    {
        $interval = (($this->grade === CertificateGrade::A) || ($this->grade === CertificateGrade::BC))
            ? 1 : 2;
        $this->next_certification_due_date = date('Y-m-d H:i:s', strtotime("+$interval year", strtotime($this->issued_at)));
        return $this;
    }

    public function setNextCertificationDueDateToNow()
    {
        $this->next_certification_due_date = date('Y-m-d H:i:s');
        return $this;
    }

    protected function findOrCreateIndicatorScore(int $indicator_id): IndicatorScore
    {
        $indicator = Indicator::findOne($indicator_id);
        if (!$indicator || ($indicator->indicatorGroup->assessment_id !== $this->assessment_id)) {
            throw new BadRequestHttpException('Indikator tidak ditemukan dalam asesmen sertifikasi saat ini');
        }

        return $this->getIndicatorScores()
            ->where([
                'indicator_id' => $indicator_id,
            ])->one()
            ?? new IndicatorScore([
                'certification_id' => $this->id,
                'indicator_id' => $indicator_id,
            ]);
    }
}
