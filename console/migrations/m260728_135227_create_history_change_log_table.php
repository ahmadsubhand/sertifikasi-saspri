<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%history_change_log}}`.
 */
class m260728_135227_create_history_change_log_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%history_change_log}}', [
            'id' => $this->primaryKey(),
            'history_id' => $this->integer()->notNull(),
            'previous_data' => $this->json()->defaultValue(null),
            'new_data' => $this->json()->defaultValue(null),
            'changed_at' => $this->dateTime()->notNull(),
        ]);

        $this->createIndex(
            '{{%idx_history_log_history_id}}',
            '{{%history_change_log}}',
            'history_id'
        );

        $this->addForeignKey(
            '{{%fk_history_log}}',
            '{{%history_change_log}}',
            'history_id',
            '{{%history}}',
            'id',
            'CASCADE',
            'RESTRICT'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%fk_history_log}}', '{{%history_change_log}}');
        $this->dropIndex('{{%idx_history_log_history_id}}', '{{%history_change_log}}');
        $this->dropTable('{{%history_change_log}}');
    }
}
