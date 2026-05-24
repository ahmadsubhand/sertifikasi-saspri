<?php

namespace common\models;

use common\enums\ApprovalStatus;
use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use common\helpers\FileHelper;
use DateTime;
use Exception;
use Yii;
use yii\behaviors\TimestampBehavior;
use yii\db\ActiveQuery;
use yii\web\UnprocessableEntityHttpException;
use yii\web\UploadedFile;

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
 *
 * @property Certification[] $certifications
 * @property User $coordinator
 * @property District $district
 * @property User $newCoordinator
 * @property SaspriKDocument[] $saspriKDocuments
 * @property User[] $users
 * @property Certification $validCertificate
 * @property Certification|null $onGoingCertification
 * @property Certification|null $latestCompletedCertification
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
            [['request_rejection_reason', 'new_coordinator_id', 'change_request_reason', 'change_rejection_reason'], 'default', 'value' => null],
            [['change_status'], 'default', 'value' => 'pending'],
            [['coordinator_id', 'district_id', 'region_name', 'address', 'cooperative_name', 'number_of_groups', 'number_of_active_members', 'livestock_type', 'total_livestock_count', 'breeding_livestock_count', 'productive_heifer_count'], 'required'],
            [['coordinator_id', 'district_id', 'number_of_groups', 'number_of_active_members', 'total_livestock_count', 'breeding_livestock_count', 'productive_heifer_count', 'new_coordinator_id'], 'integer'],
            [['region_name', 'address', 'cooperative_name', 'livestock_type', 'request_status', 'request_rejection_reason', 'change_status', 'change_request_reason', 'change_rejection_reason'], 'string', 'max' => 255],
            [['coordinator_id'], 'unique'],
            [['new_coordinator_id'], 'unique'],
            [['coordinator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['coordinator_id' => 'id']],
            [['district_id'], 'exist', 'skipOnError' => true, 'targetClass' => District::class, 'targetAttribute' => ['district_id' => 'id']],
            [['new_coordinator_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::class, 'targetAttribute' => ['new_coordinator_id' => 'id']],
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
        return $this->hasOne(Certification::class, ['saspri_k_id' => 'id'])
            ->andWhere(['not in', 'grade', [CertificateGrade::BC, CertificateGrade::C]])
            ->orderBy(['issued_at' => SORT_DESC]);
    }

    public function getOnGoingCertification(): ActiveQuery
    {
        return $this->hasOne(Certification::class, ['saspri_k_id' => 'id'])
            ->andWhere(['!=', 'status', CertificationStatus::COMPLETED]);
    }

    public function getLatestCompletedCertification(): ActiveQuery
    {
        return $this->hasOne(Certification::class, ['saspri_k_id' => 'id'])
            ->andWhere(['status' => CertificationStatus::COMPLETED])
            ->orderBy(['issued_at' => SORT_DESC]);
    }

    public function findOrCreateOnGoingCertification()
    {
        try {
            $certification = $this->onGoingCertification ?: $this->createNewCertificationRequest();
            return $certification;
        } catch (Exception $error) {
            if ($error instanceof UnprocessableEntityHttpException) {
                throw new UnprocessableEntityHttpException($error->getMessage());
            }
            throw $error;
        }
    }

    public function createNewCertificationRequest(): Certification
    {
        $last_certification = $this->latestCompletedCertification;
        if (!$last_certification) {
            throw new UnprocessableEntityHttpException(
                'Belum mendaftarkan sertifikasi level pertama (Natalia)'
            );
        }

        $next_certification_date = new DateTime($last_certification->next_certification_due_date);
        $today = new DateTime('today');
        if ($next_certification_date > $today) {
            throw new UnprocessableEntityHttpException(
                'Sertifikasi baru bisa dilakukan setelah tanggal ' . $next_certification_date->format('Y-m-d')
            );
        }

        $certification = new Certification();
        if ($last_certification->grade === CertificateGrade::BC || $last_certification->grade === CertificateGrade::C) {
            $certification->purpose = CertificationPurpose::RENEWAL;
            $certification->level = $last_certification->level;
        } else {
            $certification->purpose = CertificationPurpose::LEVEL_UP;
            switch ($last_certification->level) {
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
                'Belum ada instrumen penilaian aktif untuk sertifikasi tingkat ' .
                CertificateLevel::list()[$certification->level]
            );
        }

        $certification->assessment_id = $assessment->id;
        $certification->saspri_k_id = $this->id;
        $certification->self_team_due_date = date('Y-m-d H:i:s', strtotime('+1 week'));
        $certification->status = CertificationStatus::PENDING_SELF_TEAM_FORMATION;

        return $certification;
    }

    public function createCertificationForNewSaspriK(): Certification
    {
        $certification = new Certification();
        $certification->purpose = CertificationPurpose::LEVEL_UP;
        $certification->status = CertificationStatus::COMPLETED;
        $certification->level = CertificateLevel::NATALIA;
        $certification->saspri_k_id = $this->id;

        $assessment = Assessment::findOne(['active_at_level' => $certification->level]);
        if (!$assessment) {
            throw new UnprocessableEntityHttpException(
                'Belum ada instrumen penilaian aktif untuk sertifikasi tingkat ' .
                CertificateLevel::list()[$certification->level]
            );
        }
        $certification->assessment_id = Assessment::findOne(['active_at_level' => $certification->level])->id;

        return $certification;
    }

    public function addMembers(array $user_ids)
    {
        User::updateAll(
            ['saspri_k_id' => $this->id],
            ['id' => $user_ids]
        );
    }

    /**
     * @param UploadedFile[] $documents
     * @param string[] $types
     */

    public function uploadNewDocuments(array $documents, array $types)
    {
        $relative_dir = '/uploads/document/' . $this->id;
        $absolute_dir = Yii::getAlias('@frontend/web' . $relative_dir);
        FileHelper::ensureDirectoryExists($absolute_dir);

        foreach ($documents as $index => $document) {
            $fileName = $this->generateDocumentFileName($document->extension);
    
            if ($document->saveAs($absolute_dir . '/' . $fileName)) {
                $saspri_k_document = new SaspriKDocument();
                $saspri_k_document->type = $types[$index];
                $saspri_k_document->url = $relative_dir . '/' . $fileName;
                $saspri_k_document->saspri_k_id = $this->id;
                $saspri_k_document->save(false);
            } else {
                throw new Exception('Failed to save file saspri k document');
            }
        }
    }

    protected function generateDocumentFileName(string $extension): string
    {
        return sprintf(
            'doc_%d_%d.%s',
            $this->id,
            time(),
            $extension,
        );
    }

    public function deleteOldDocuments(): void
    {
        $old_documents = $this->saspriKDocuments;
        if (empty($old_documents)) {
            return;
        }
        // dd($old_documents);
        foreach ($old_documents as $old_document) {
            $oldFile = Yii::getAlias('@frontend/web' . $old_document->url);
            FileHelper::deleteFile($oldFile);
            $old_document->delete();
        }
    }

    public function requestCoordinatorChange(int $new_coordinator_id, string $reason): self
    {
        $this->new_coordinator_id = $new_coordinator_id;
        $this->change_request_reason = $reason;
        $this->change_status = ApprovalStatus::PENDING;
        $this->change_rejection_reason = null;
        return $this;
    }

    public function approveCoordinatorChange(): self
    {
        $this->coordinator_id = $this->new_coordinator_id;
        $this->new_coordinator_id = null;
        $this->change_status = ApprovalStatus::APPROVED;
        $this->change_request_reason = null;
        $this->change_rejection_reason = null;
        return $this;
    }

    public function rejectCoordinatorChange(string $reason): self
    {
        $this->change_status = ApprovalStatus::REJECTED;
        $this->change_rejection_reason = $reason;
        return $this;
    }

    public function requestRegistration(int $coordinator_id): self
    {
        $this->coordinator_id = $coordinator_id;
        $this->request_status = ApprovalStatus::PENDING;
        $this->request_rejection_reason = null;
        return $this;
    }

    public function approveRegistration(): self
    {
        $this->request_status = ApprovalStatus::APPROVED;
        $this->request_rejection_reason = null;
        return $this;
    }

    public function rejectRegistration(string $reason): self
    {
        $this->request_status = ApprovalStatus::REJECTED;
        $this->request_rejection_reason = $reason;
        return $this;
    }
}
