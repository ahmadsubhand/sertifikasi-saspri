<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%audit_log}}`.
 */
class m260531_024702_create_audit_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('audit_log', [
            'id' => $this->primaryKey(),
            'table_name' => $this->string(255)->notNull(),
            'model_id' => $this->integer()->notNull(),
            'action' => $this->string(20)->notNull(),
            'old_values' => $this->json(),
            'new_values' => $this->json(),
            'user_id' => $this->integer(),
            'ip_address' => $this->string(45),
            'user_agent' => $this->string(255),
            'created_at' => $this->integer()->notNull(),
        ]);

        // Membuat index untuk mempercepat query pencarian log
        $this->createIndex(
            'idx-audit_log-table',
            'audit_log',
            ['table_name', 'model_id']
        );

        $this->createIndex(
            'idx-audit_log-user_id',
            'audit_log',
            'user_id'
        );

        // Opsional: Foreign key ke tabel user (jika user dihapus, set null agar log tetap ada)
        $this->addForeignKey(
            'fk-audit_log-user_id',
            'audit_log',
            'user_id',
            'user',
            'id',
            'SET NULL',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-audit_log-user_id', 'audit_log');
        $this->dropIndex('idx-audit_log-user_id', 'audit_log');
        $this->dropIndex('idx-audit_log-table', 'audit_log');
        
        $this->dropTable('audit_log');
    }
}
