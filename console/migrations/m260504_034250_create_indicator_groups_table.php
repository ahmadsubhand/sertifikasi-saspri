<?php

use yii\db\Migration;

/**
 * Handles the creation of table `indicator_groups`.
 * Has foreign keys to the tables:
 *
 * - `parent_group_id`
 */
class m260504_034250_create_indicator_groups_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicator_groups', [
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
            'idx-indicator_groups-parent_group_id',
            'indicator_groups',
            'parent_group_id'
        );

        // add foreign key for table `indicator_groups`
        $this->addForeignKey(
            'fk-indicator_groups-parent_group_id',
            'indicator_groups',
            'parent_group_id',
            'indicator_groups',
            'id',
            'CASCADE'
        );

        // creates index for column `assessment_id`
        $this->createIndex(
            'idx-indicator_groups-assessment_id',
            'indicator_groups',
            'assessment_id'
        );

        // add foreign key for table `assessments`
        $this->addForeignKey(
            'fk-assessments-assessment_id',
            'indicator_groups',
            'assessment_id',
            'assessments',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drop foreign key for table `assessments`
        $this->dropForeignKey(
            'fk-assessments-assessment_id',
            'indicator_groups',
        );

        // drop index for column `assessment_id`
        $this->dropIndex(
            'idx-assessments-assessment_id',
            'assessments',
        );

        // drops foreign key for table `indicator_groups`
        $this->dropForeignKey(
            'fk-indicator_groups-parent_group_id',
            'indicator_groups'
        );

        // drops index for column `parent_group_id`
        $this->dropIndex(
            'idx-indicator_groups-parent_group_id',
            'indicator_groups'
        );

        $this->dropTable('indicator_groups');
    }
}
