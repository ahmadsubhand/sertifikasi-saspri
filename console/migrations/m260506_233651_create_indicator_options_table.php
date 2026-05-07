<?php

use yii\db\Migration;

/**
 * Handles the creation of table `indicator_options`.
 * Has foreign keys to the tables:
 *
 * - `indicators`
 */
class m260506_233651_create_indicator_options_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicator_options', [
            'id' => $this->primaryKey(),
            'indicator_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'label' => $this->string()->notNull(),
            'order' => $this->integer()->notNull(),
            'weight' => $this->integer()->notNull(),
        ]);

        // creates index for column `indicator_id`
        $this->createIndex(
            'idx-indicator_options-indicator_id',
            'indicator_options',
            'indicator_id'
        );

        // add foreign key for table `indicators`
        $this->addForeignKey(
            'fk-indicator_options-indicator_id',
            'indicator_options',
            'indicator_id',
            'indicators',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `indicators`
        $this->dropForeignKey(
            'fk-indicator_options-indicator_id',
            'indicator_options'
        );

        // drops index for column `indicator_id`
        $this->dropIndex(
            'idx-indicator_options-indicator_id',
            'indicator_options'
        );

        $this->dropTable('indicator_options');
    }
}
