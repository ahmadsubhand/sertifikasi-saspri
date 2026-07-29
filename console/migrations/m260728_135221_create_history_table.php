<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%history}}`.
 */
class m260728_135221_create_history_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%history}}', [
            'id' => $this->primaryKey(),
            'date' => $this->dateTime()->notNull(),
            'livestock_id' => $this->integer()->notNull(),
            'sell_price' => $this->integer()->notNull()->defaultValue(0),
            'pedet_price' => $this->integer()->defaultValue(0),
            'additional_cost' => $this->integer()->defaultValue(0),
            'business_type' => $this->string(20)->defaultValue('penggemukan'),
            'hpp_price' => $this->integer()->notNull()->defaultValue(0),
            'forage_price' => $this->integer()->defaultValue(0),
            'concentrate_price' => $this->integer()->defaultValue(0),
            'additive_price' => $this->integer()->defaultValue(0),
            'insemination' => $this->integer()->defaultValue(0),
            'vaccine' => $this->integer()->defaultValue(0),
            'vitamin' => $this->integer()->defaultValue(0),
            'pregnancy_check' => $this->integer()->defaultValue(0),
            'antibiotics' => $this->integer()->defaultValue(0),
            'anthelmintic' => $this->integer()->defaultValue(0),
            'cage_price' => $this->integer()->defaultValue(0),
            'cage_productive_age' => $this->integer()->defaultValue(0),
            'workers_price' => $this->integer()->defaultValue(0),
            'workers_per_livestock' => $this->integer()->defaultValue(0),
            'margin' => $this->integer()->defaultValue(0),
            'inflation' => $this->integer()->defaultValue(0),
            'number_of_workers' => $this->integer()->defaultValue(0),
        ]);

        $this->createIndex(
            '{{%idx_history_livestock_id}}',
            '{{%history}}',
            'livestock_id'
        );

        $this->addForeignKey(
            '{{%fk_history_livestock}}',
            '{{%history}}',
            'livestock_id',
            '{{%livestock}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_history_livestock}}', '{{%history}}');
        $this->dropIndex('{{%idx_history_livestock_id}}', '{{%history}}');
        $this->dropTable('{{%history}}');
    }
}
