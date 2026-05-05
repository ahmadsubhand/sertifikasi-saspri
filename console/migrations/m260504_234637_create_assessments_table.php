<?php

use yii\db\Migration;

/**
 * Handles the creation of table `assessments`.
 */
class m260504_234637_create_assessments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('assessments', [
            'id' => $this->primaryKey(),
            'title' => $this->string()->notNull(),
            'is_active' => $this->boolean()->defaultValue(0),
            'created_at' => $this->dateTime()->notNull(),
            'updated_at' => $this->dateTime()->notNull(),
            'released_at' => $this->dateTime(),
        ]);

        // only one assessment active
        $this->createIndex(
            'idx-unique-active-assessment',
            'assessments',
            'is_active',
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'idx-unique-active-assessment',
            'assessments',
        );

        $this->dropTable('assessments');
    }
}
