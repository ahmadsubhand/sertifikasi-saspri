<?php

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use yii\db\Migration;

/**
 * Handles the creation of table `certification`.
 * Has foreign keys to the tables:
 *
 * - `saspri_k`
 */
class m260504_042603_create_certification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('certification', [
            'id' => $this->primaryKey(),
            'saspri_k_id' => $this->integer()->notNull(),
            'assessment_id' => $this->integer()->notNull(),

            // Berkaitan dengan proses sertifikasi
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'purpose' => $this->string()->notNull(),
            'status' => $this->string()->notNull()->defaultValue(CertificationStatus::PENDING_SELF_TEAM_FORMATION),
            'self_team_due_date' => $this->dateTime(),
            'self_review_due_date' => $this->dateTime(),
            'peer_team_due_date' => $this->dateTime(),
            'peer_review_due_date' => $this->dateTime(),
            'external_review_due_date' => $this->dateTime(),

            // Informasi hasil sertifikasi
            'level' => $this->string()->notNull(),
            'code' => $this->string(), // apakah generate hanya ketika sudah terbit?
            'issued_at' => $this->dateTime(),
            'total_score' => $this->integer()->defaultValue(0),
            'grade' => $this->string(),
            'next_certification_due_date' => $this->dateTime(),
            'is_rejected' => $this->boolean()->defaultValue(false),
            'rejection_reason' => $this->string(),
        ]);

        // creates index for column `saspri_k_id`
        $this->createIndex(
            'idx-certification-saspri_k_id',
            'certification',
            'saspri_k_id'
        );

        // add foreign key for table `saspri_k`
        $this->addForeignKey(
            'fk-certification-saspri_k_id',
            'certification',
            'saspri_k_id',
            'saspri_k',
            'id',
            'CASCADE'
        );

        // creates index for column `assessment_id`
        $this->createIndex(
            'idx-certification-assessment_id',
            'certification',
            'assessment_id'
        );

        // add foreign key for table `assessment`
        $this->addForeignKey(
            'fk-certification-assessment_id',
            'certification',
            'assessment_id',
            'assessment',
            'id',
            'CASCADE'
        );

        // purpose
        $this->addCheck(
            'chk-certification-purpose',
            'certification',
            "purpose IN ('" . implode("','", CertificationPurpose::values()) . "')"
        );

        // status
        $this->addCheck(
            'chk-certification-status',
            'certification',
            "status IN ('" . implode("','", CertificationStatus::values()) . "')"
        );

        // level
        $this->addCheck(
            'chk-certification-level',
            'certification',
            "level IN ('" . implode("','", CertificateLevel::values()) . "')"
        );

        // grade (nullable)
        $this->addCheck(
            'chk-certification-grade',
            'certification',
            "grade IS NULL OR grade IN ('" . implode("','", CertificateGrade::values()) . "')"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // purpose
        $this->dropCheck(
            'chk-certification-purpose',
            'certification',
        );

        // status
        $this->dropCheck(
            'chk-certification-status',
            'certification',
        );

        // level
        $this->dropCheck(
            'chk-certification-level',
            'certification',
        );

        // grade (nullable)
        $this->dropCheck(
            'chk-certification-grade',
            'certification',
        );

        // drops foreign key for table `assessment`
        $this->dropForeignKey(
            'fk-certification-assessment_id',
            'certification'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            'idx-certification-assessment_id',
            'certification'
        );

        // drops foreign key for table `saspri_k`
        $this->dropForeignKey(
            'fk-certification-saspri_k_id',
            'certification'
        );

        // drops index for column `saspri_k_id`
        $this->dropIndex(
            'idx-certification-saspri_k_id',
            'certification'
        );

        $this->dropTable('certification');
    }
}
