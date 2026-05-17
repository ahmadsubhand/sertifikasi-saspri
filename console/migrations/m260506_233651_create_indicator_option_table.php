<?php

use yii\db\Migration;

/**
 * Handles the creation of table `indicator_option`.
 * Has foreign keys to the tables:
 *
 * - `indicator`
 */
class m260506_233651_create_indicator_option_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicator_option', [
            'id' => $this->primaryKey(),
            'indicator_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'label' => $this->string()->notNull(),
            'order' => $this->integer()->notNull(),
            'weight' => $this->integer()->notNull(),
        ]);

        // creates index for column `indicator_id`
        $this->createIndex(
            'idx-indicator_option-indicator_id',
            'indicator_option',
            'indicator_id'
        );

        // add foreign key for table `indicator`
        $this->addForeignKey(
            'fk-indicator_option-indicator_id',
            'indicator_option',
            'indicator_id',
            'indicator',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `indicator`
        $this->dropForeignKey(
            'fk-indicator_option-indicator_id',
            'indicator_option'
        );

        // drops index for column `indicator_id`
        $this->dropIndex(
            'idx-indicator_option-indicator_id',
            'indicator_option'
        );

        $this->dropTable('indicator_option');
    }
}
