<?php

use common\enums\CertificateLevel;
use yii\db\Migration;

/**
 * Handles the creation of table `assessment`.
 */
class m260504_034225_create_assessment_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('assessment', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'active_at_level' => $this->string(),
            'level' => $this->string()->notNull(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
            'released_at' => $this->dateTime(),
        ]);

        // each level only one assessment active allowed
        $this->addCheck(
            'chk-assessment-active_at_level',
            'assessment',
            "active_at_level IS NULL OR active_at_level IN ('" . implode("','", CertificateLevel::values()) . "')"
        );

        // creates index for column `active_at_level`
        $this->createIndex(
            'idx-assessment-active_at_level',
            'assessment',
            'active_at_level',
            true,
        );

        // level
        $this->addCheck(
            'chk-assessment-level',
            'assessment',
            "level IN ('" . implode("','", CertificateLevel::values()) . "')"
        );

        $this->addCheck(
            'chk-assessment-active_level_match',
            'assessment',
            'active_at_level IS NULL OR active_at_level = level'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCheck(
            'chk-assessment-active_level_match',
            'assessment',
        );

        $this->dropCheck(
            'chk-assessment-level',
            'assessment',
        );

        $this->dropIndex(
            'idx-assessment-active_at_level',
            'assessment',
        );

        $this->dropCheck(
            'chk-assessment-active_at_level',
            'assessment',
        );

        $this->dropTable('assessment');
    }
}
