<?php

use common\enums\CertificateLevel;
use yii\db\Migration;

/**
 * Handles the creation of table `assessments`.
 */
class m260504_034225_create_assessments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('assessments', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'active_at_level' => $this->string(),
            'level' => $this->string()->notNull(),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'released_at' => $this->dateTime(),
        ]);

        // each level only one assessment active allowed
        $this->addCheck(
            'chk-assessments-active_at_level',
            'assessments',
            "active_at_level IS NULL OR active_at_level IN ('" . implode("','", CertificateLevel::values()) . "')"
        );

        // creates index for column `active_at_level`
        $this->createIndex(
            'idx-assessments-active_at_level',
            'assessments',
            'active_at_level',
            true,
        );

        // level
        $this->addCheck(
            'chk-assessments-level',
            'assessments',
            "level IN ('" . implode("','", CertificateLevel::values()) . "')"
        );

        $this->addCheck(
            'chk-assessments-active_level_match',
            'assessments',
            'active_at_level IS NULL OR active_at_level = level'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCheck(
            'chk-assessments-active_level_match',
            'assessments',
        );

        $this->dropCheck(
            'chk-assessments-level',
            'assessments',
        );

        $this->dropIndex(
            'idx-assessments-active_at_level',
            'assessments',
        );

        $this->dropCheck(
            'chk-assessments-active_at_level',
            'assessments',
        );

        $this->dropTable('assessments');
    }
}
