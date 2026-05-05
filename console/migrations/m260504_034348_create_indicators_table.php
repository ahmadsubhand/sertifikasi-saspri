<?php

use yii\db\Migration;

/**
 * Handles the creation of table `indicators`.
 * Has foreign keys to the tables:
 *
 * - `indicator_groups`
 */
class m260504_034348_create_indicators_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicators', [
            'id' => $this->primaryKey(),
            'indicator_group_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'label' => $this->string()->notNull(),
            'order' => $this->integer()->notNull(),
        ]);

        // creates index for column `indicator_group_id`
        $this->createIndex(
            'idx-indicators-indicator_group_id',
            'indicators',
            'indicator_group_id'
        );

        // add foreign key for table `indicator_groups`
        $this->addForeignKey(
            'fk-indicators-indicator_group_id',
            'indicators',
            'indicator_group_id',
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
            'fk-indicators-indicator_group_id',
            'indicators'
        );

        // drops index for column `indicator_group_id`
        $this->dropIndex(
            'idx-indicators-indicator_group_id',
            'indicators'
        );

        $this->dropTable('indicators');
    }
}
