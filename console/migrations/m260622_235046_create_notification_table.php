<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%notification}}`.
 */
class m260622_235046_create_notification_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%notification}}', [
            'id' => $this->primaryKey(),
            'recipient_id' => $this->integer()->notNull(),
            'sender_id' => $this->integer(),
            'title' => $this->string(255)->notNull(),
            'body' => $this->text()->notNull(),
            'web_link' => $this->string(255),
            'api_link' => $this->string(255),
            'read_at' => $this->integer(),
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            'idx-notification-recipient_id',
            '{{%notification}}',
            'recipient_id'
        );

        $this->addForeignKey(
            'fk-notification-recipient_id',
            '{{%notification}}',
            'recipient_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'idx-notification-sender_id',
            '{{%notification}}',
            'sender_id'
        );

        $this->addForeignKey(
            'fk-notification-sender_id',
            '{{%notification}}',
            'sender_id',
            'user',
            'id',
            'SET NULL'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('fk-notification-sender_id', '{{%notification}}');
        $this->dropIndex('idx-notification-sender_id', '{{%notification}}');
        $this->dropForeignKey('fk-notification-recipient_id', '{{%notification}}');
        $this->dropIndex('idx-notification-recipient_id', '{{%notification}}');
        $this->dropTable('{{%notification}}');
    }
}
