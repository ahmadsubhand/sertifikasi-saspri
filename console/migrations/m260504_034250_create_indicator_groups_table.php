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
            'parent_group_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'label' => $this->string()->notNull(),
            'order' => $this->integer()->notNull(),
            'weight' => $this->double()->notNull(),
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
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
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
