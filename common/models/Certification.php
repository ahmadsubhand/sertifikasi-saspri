<?php

namespace common\models;

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use DateTime;
use Error;
use Exception;
use Yii;
use yii\web\UnauthorizedHttpException;

/**
 * This is the model class for table "certifications".
 *
 * @property int $id
 * @property int $saspri_k_id
 * @property string $purpose
 * @property string $submitted_at
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
 * @property SaspriK $saspriK
 * @property SaspriK $saspriK0
 * @property SelfTeamMember[] $selfTeamMembers
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
        return 'certifications';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            // [['self_team_due_date', 'self_review_due_date', 'peer_team_due_date', 'peer_review_due_date', 'external_review_due_date', 'issued_at', 'grade', 'next_certification_due_date', 'rejection_reason'], 'default', 'value' => null],
            // [['is_rejected'], 'default', 'value' => 0],
            [['saspri_k_id', 'purpose', 'level', 'assessment_id'], 'required'],
            [['saspri_k_id', 'total_score', 'is_rejected', 'assessment_id'], 'integer'],
            [['submitted_at', 'self_team_due_date', 'self_review_due_date', 'peer_team_due_date', 'peer_review_due_date', 'external_review_due_date', 'issued_at', 'next_certification_due_date'], 'safe'],
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
            'purpose' => 'Purpose',
            'submitted_at' => 'Submitted At',
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

    public function setNewCertificationRequest(SaspriK $saspri_k)
    {
        $valid_certificate = $saspri_k->validCertificate;

        $next_certification_date = new DateTime($valid_certificate->next_certification_due_date);
        $today = new DateTime('today');
        if ($next_certification_date > $today) {
            throw new Exception('not time yet|' . date('Y-m-d', strtotime($valid_certificate->next_certification_due_date)));
        }

        $this->saspri_k_id = $saspri_k->id;

        if ($valid_certificate->grade === CertificateGrade::C || $valid_certificate->grade === CertificateGrade::D) {
            $this->purpose = CertificationPurpose::RENEWAL;
            $this->level = $valid_certificate->level;
        } else {
            $this->purpose = CertificationPurpose::LEVEL_UP;
            switch ($valid_certificate->level) {
                case CertificateLevel::NATALIA:
                    $this->level = CertificateLevel::WEANIA;
                    break;
                case CertificateLevel::WEANIA:
                    $this->level = CertificateLevel::PREMATURA;
                    break;
                case CertificateLevel::PREMATURA:
                    $this->level = CertificateLevel::MATURA;
                    break;
                default:
                    throw new Error('Maximum level has been reached');
            }
        }

        $assessment_id = Assessment::findOne(['is_active' => true])->id;
        $this->assessment_id = $assessment_id;
        
        // Sudah di handle db melalui defaultValue di migrasi
        // $this->status = CertificationStatus::PENDING_SELF_TEAM_FORMATION

        $this->self_team_due_date = date('Y-m-d H:i:s', strtotime('+1 week'));
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
        return $this->hasMany(Indicator::class, ['id' => 'indicator_id'])->viaTable('indicator_scores', ['certification_id' => 'id']);
    }

    /**
     * Gets query for [[PeerTeamMembers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getPeerTeamMembers()
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
     * Gets query for [[SaspriK0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSaspriK0()
    {
        return $this->hasOne(SaspriK::class, ['valid_certificate_id' => 'id']);
    }

    /**
     * Gets query for [[SelfTeamMembers]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSelfTeamMembers()
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
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('peer_team_members', ['certification_id' => 'id']);
    }

    /**
     * Gets query for [[Users0]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers0()
    {
        return $this->hasMany(User::class, ['id' => 'user_id'])->viaTable('self_team_members', ['certification_id' => 'id']);
    }

}
