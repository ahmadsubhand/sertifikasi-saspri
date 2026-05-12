<?php

namespace common\models;

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use DateTime;
use yii\db\ActiveQuery;
use yii\web\UnprocessableEntityHttpException;

/**
 * This is the model class for table "saspri_k".
 *
 * @property int $id
 * @property int $coordinator_id
 * @property int $district_id
 * @property string $region_name
 * @property string $address
 * @property string $cooperative_name
 * @property int $number_of_groups
 * @property int $number_of_active_members
 * @property string $livestock_type
 * @property int $total_livestock_count
 * @property int $breeding_livestock_count
 * @property int $productive_heifer_count
 * @property string $request_status
 * @property string|null $request_rejection_reason
 * @property string $change_status
 * @property int|null $new_coordinator_id
 * @property string|null $change_request_reason
 * @property string|null $change_rejection_reason
 * @property int $valid_certificate_id
 *
 * @property Certification[] $certifications
 * @property User $coordinator
 * @property District $district
 * @property User $newCoordinator
 * @property SaspriKDocument[] $saspriKDocuments
 * @property User[] $users
 * @property Certification $validCertificate
 * @property Certification|null $onGoingCertification
 */
class SaspriK extends \yii\db\ActiveRecord
{
    /**
     * {@inheritdoc}
     */
    public static function tableName()
    {
        return 'saspri_k';
    }

    /**
     * {@inheritdoc}
     */
    public function rules()
    {
        return [
            [['request_rejection_reason', 'new_coordinator_id', 'change_request_reason', 'change_rejection_reason'], 'default', 'value' => null],
            [['change_status'], 'default', 'value' => 'pending'],
            [['coordinator_id', 'district_id', 'region_name', 'address', 'cooperative_name', 'number_of_groups', 'number_of_active_members', 'livestock_type', 'total_livestock_count', 'breeding_livestock_count', 'productive_heifer_count', 'valid_certificate_id'], 'required'],
            [['coordinator_id', 'district_id', 'number_of_groups', 'number_of_active_members', 'total_livestock_count', 'breeding_livestock_count', 'productive_heifer_count', 'new_coordinator_id', 'valid_certificate_id'], 'integer'],
            [['region_name', 'address', 'cooperative_name', 'livestock_type', 'request_status', 'request_rejection_reason', 'change_status', 'change_request_reason', 'change_rejection_reason'], 'string', 'max' => 255],
            [['coordinator_id'], 'unique'],
            [['valid_certificate_id'], 'unique'],
            [['new_coordinator_id'], 'unique'],
            [['coordinator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['coordinator_id' => 'id']],
            [['district_id'], 'exist', 'skipOnError' => true, 'targetClass' => District::class, 'targetAttribute' => ['district_id' => 'id']],
            [['new_coordinator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['new_coordinator_id' => 'id']],
            [['valid_certificate_id'], 'exist', 'skipOnError' => true, 'targetClass' => Certification::class, 'targetAttribute' => ['valid_certificate_id' => 'id']],
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'coordinator_id' => 'Coordinator ID',
            'district_id' => 'District ID',
            'region_name' => 'Region Name',
            'address' => 'Address',
            'cooperative_name' => 'Cooperative Name',
            'number_of_groups' => 'Number Of Groups',
            'number_of_active_members' => 'Number Of Active Members',
            'livestock_type' => 'Livestock Type',
            'total_livestock_count' => 'Total Livestock Count',
            'breeding_livestock_count' => 'Breeding Livestock Count',
            'productive_heifer_count' => 'Productive Heifer Count',
            'request_status' => 'Request Status',
            'request_rejection_reason' => 'Request Rejection Reason',
            'change_status' => 'Change Status',
            'new_coordinator_id' => 'New Coordinator ID',
            'change_request_reason' => 'Change Request Reason',
            'change_rejection_reason' => 'Change Rejection Reason',
            'valid_certificate_id' => 'Valid Certificate ID',
        ];
    }

    /**
     * Gets query for [[Certifications]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCertifications()
    {
        return $this->hasMany(Certification::class, ['saspri_k_id' => 'id']);
    }

    /**
     * Gets query for [[Coordinator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getCoordinator()
    {
        return $this->hasOne(User::class, ['id' => 'coordinator_id']);
    }

    /**
     * Gets query for [[District]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getDistrict()
    {
        return $this->hasOne(District::class, ['id' => 'district_id']);
    }

    /**
     * Gets query for [[NewCoordinator]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getNewCoordinator()
    {
        return $this->hasOne(User::class, ['id' => 'new_coordinator_id']);
    }

    /**
     * Gets query for [[SaspriKDocuments]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getSaspriKDocuments()
    {
        return $this->hasMany(SaspriKDocument::class, ['saspri_k_id' => 'id']);
    }

    /**
     * Gets query for [[Users]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getUsers()
    {
        return $this->hasMany(User::class, ['saspri_k_id' => 'id']);
    }

    /**
     * Gets query for [[ValidCertificate]].
     *
     * @return \yii\db\ActiveQuery
     */
    public function getValidCertificate()
    {
        return $this->hasOne(Certification::class, ['id' => 'valid_certificate_id']);
    }

    public function getOnGoingCertification(): ActiveQuery
    {
        return $this->hasOne(Certification::class, ['saspri_k_id' => 'id'])
            ->andWhere(['!=', 'status', CertificationStatus::COMPLETED]);
    }

    public function createNewCertificationRequest(): Certification
    {
        $valid_certificate = $this->validCertificate;

        $next_certification_date = new DateTime($valid_certificate->next_certification_due_date);
        $today = new DateTime('today');
        if ($next_certification_date > $today) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi baru bisa dilakukan setelah tanggal ' . $next_certification_date->format('Y-m-d')
            );
        }

        $certification = new Certification();
        if ($valid_certificate->grade === CertificateGrade::C || $valid_certificate->grade === CertificateGrade::D) {
            $certification->purpose = CertificationPurpose::RENEWAL;
            $certification->level = $valid_certificate->level;
        } else {
            $certification->purpose = CertificationPurpose::LEVEL_UP;
            switch ($valid_certificate->level) {
                case CertificateLevel::NATALIA:
                    $certification->level = CertificateLevel::WEANIA;
                    break;
                case CertificateLevel::WEANIA:
                    $certification->level = CertificateLevel::PREMATURA;
                    break;
                case CertificateLevel::PREMATURA:
                    $certification->level = CertificateLevel::MATURA;
                    break;
                default:
                    throw new UnprocessableEntityHttpException('Sertifikasi sudah mencapai tingkat paling tinggi');
            }
        }

        $assessment = Assessment::findOne(['active_at_level' => $certification->level]);
        if (!$assessment) {
            throw new UnprocessableEntityHttpException(
                'Belum ada instrumen penilaian aktif untuk sertifikasi tingkat ' . ucfirst($certification->level)
            );
        }

        $certification->assessment_id = $assessment->id;
        $certification->saspri_k_id = $this->id;
        $certification->self_team_due_date = date('Y-m-d H:i:s', strtotime('+1 week'));
        $certification->status = CertificationStatus::PENDING_SELF_TEAM_FORMATION;

        return $certification;
    }

    public function addMembers(array $user_ids) 
    {
        User::updateAll(
            ['saspri_k_id' => $this->id],
            ['id' => $user_ids]
        );
    }
}
