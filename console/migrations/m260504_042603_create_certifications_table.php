<?php

use common\enums\CertificateGrade;
use common\enums\CertificateLevel;
use common\enums\CertificationPurpose;
use common\enums\CertificationStatus;
use yii\db\Migration;

/**
 * Handles the creation of table `certifications`.
 * Has foreign keys to the tables:
 *
 * - `saspri_k`
 */
class m260504_042603_create_certifications_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('certifications', [
            'id' => $this->primaryKey(),
            'saspri_k_id' => $this->integer()->notNull(),

            // Berkaitan dengan proses sertifikasi
            'purpose' => $this->string()->notNull(),
            'submitted_at' => $this->dateTime()->notNull(),
            'status' => $this->string()->notNull(),
            'self_team_due_date' => $this->dateTime(),
            'self_review_due_date' => $this->dateTime(),
            'peer_team_due_date' => $this->dateTime(),
            'peer_review_due_date' => $this->dateTime(),
            'external_review_due_date' => $this->dateTime(),

            // Informasi hasil sertifikasi
            'level' => $this->string()->notNull(),
            'code' => $this->string()->notNull(), // apakah generate hanya ketika sudah terbit?
            'issued_at' => $this->dateTime(),
            'total_score' => $this->integer()->defaultValue(0),
            'grade' => $this->string(),
            'next_certification_due_date' => $this->dateTime(),
        ]);

        // creates index for column `saspri_k_id`
        $this->createIndex(
            'idx-certifications-saspri_k_id',
            'certifications',
            'saspri_k_id'
        );

        // add foreign key for table `saspri_k`
        $this->addForeignKey(
            'fk-certifications-saspri_k_id',
            'certifications',
            'saspri_k_id',
            'saspri_k',
            'id',
            'CASCADE'
        );

        // purpose
        $this->addCheck(
            'chk-certifications-purpose',
            'certifications',
            "purpose IN ('" . implode("','", CertificationPurpose::values()) . "')"
        );

        // status
        $this->addCheck(
            'chk-certifications-status',
            'certifications',
            "status IN ('" . implode("','", CertificationStatus::values()) . "')"
        );

        // level
        $this->addCheck(
            'chk-certifications-level',
            'certifications',
            "level IN ('" . implode("','", CertificateLevel::values()) . "')"
        );

        // grade (nullable)
        $this->addCheck(
            'chk-certifications-grade',
            'certifications',
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
            'chk-certifications-purpose',
            'certifications',
        );

        // status
        $this->dropCheck('chk-certifications-status', 'certifications',
        );
        
        // level
        $this->dropCheck('chk-certifications-level', 'certifications',
        );

        // grade (nullable)
        $this->dropCheck('chk-certifications-grade', 'certifications',
        );
        
        // drops foreign key for table `saspri_k`
        $this->dropForeignKey(
            'fk-certifications-saspri_k_id',
            'certifications'
        );

        // drops index for column `saspri_k_id`
        $this->dropIndex(
            'idx-certifications-saspri_k_id',
            'certifications'
        );

        $this->dropTable('certifications');
    }
}
