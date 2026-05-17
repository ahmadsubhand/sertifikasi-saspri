<?php

use yii\db\Migration;

/**
 * Handles the creation of table `indicator_group`.
 * Has foreign keys to the tables:
 *
 * - `parent_group_id`
 */
class m260504_034250_create_indicator_group_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicator_group', [
            'id' => $this->primaryKey(),
            'assessment_id' => $this->integer(),
            'parent_group_id' => $this->integer(),
            'code' => $this->string()->notNull(),
            'label' => $this->string()->notNull(),
            'order' => $this->integer()->notNull(),
            'weight' => $this->integer()->notNull(),
        ]);

        // creates index for column `parent_group_id`
        $this->createIndex(
            'idx-indicator_group-parent_group_id',
            'indicator_group',
            'parent_group_id'
        );

        // add foreign key for table `indicator_group`
        $this->addForeignKey(
            'fk-indicator_group-parent_group_id',
            'indicator_group',
            'parent_group_id',
            'indicator_group',
            'id',
            'CASCADE'
        );

        // creates index for column `assessment_id`
        $this->createIndex(
            'idx-indicator_group-assessment_id',
            'indicator_group',
            'assessment_id'
        );

        // add foreign key for table `assessment`
        $this->addForeignKey(
            'fk-assessment-assessment_id',
            'indicator_group',
            'assessment_id',
            'assessment',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drop foreign key for table `assessment`
        $this->dropForeignKey(
            'fk-assessment-assessment_id',
            'indicator_group',
        );

        // drop index for column `assessment_id`
        $this->dropIndex(
            'idx-indicator_group-assessment_id',
            'indicator_group',
        );

        // drops foreign key for table `indicator_group`
        $this->dropForeignKey(
            'fk-indicator_group-parent_group_id',
            'indicator_group'
        );

        // drops index for column `parent_group_id`
        $this->dropIndex(
            'idx-indicator_group-parent_group_id',
            'indicator_group'
        );

        $this->dropTable('indicator_group');
    }
}
