<?php

use yii\db\Migration;

/**
 * Handles the creation of table `indicator`.
 * Has foreign keys to the tables:
 *
 * - `indicator_group`
 */
class m260504_034348_create_indicator_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicator', [
            'id' => $this->primaryKey(),
            'indicator_group_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'label' => $this->string()->notNull(),
            'order' => $this->integer()->notNull(),
        ]);

        // creates index for column `indicator_group_id`
        $this->createIndex(
            'idx-indicator-indicator_group_id',
            'indicator',
            'indicator_group_id'
        );

        // add foreign key for table `indicator_group`
        $this->addForeignKey(
            'fk-indicator-indicator_group_id',
            'indicator',
            'indicator_group_id',
            'indicator_group',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `indicator_group`
        $this->dropForeignKey(
            'fk-indicator-indicator_group_id',
            'indicator'
        );

        // drops index for column `indicator_group_id`
        $this->dropIndex(
            'idx-indicator-indicator_group_id',
            'indicator'
        );

        $this->dropTable('indicator');
    }
}
