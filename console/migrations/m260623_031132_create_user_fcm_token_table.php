<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%user_fcm_token}}`.
 */
class m260623_031132_create_user_fcm_token_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%user_fcm_token}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->notNull(),
            'token' => $this->string(500)->notNull(), // Token FCM biasanya cukup panjang
            'device_type' => $this->string(50)->null(), // Opsional: untuk mencatat 'android', 'ios', atau 'web'
            'created_at' => $this->integer()->notNull(),
            'updated_at' => $this->integer()->notNull(),
        ]);

        $this->createIndex(
            '{{%idx-user_fcm_token-user_id}}',
            '{{%user_fcm_token}}',
            'user_id'
        );

        $this->addForeignKey(
            '{{%fk-user_fcm_token-user_id}}',
            '{{%user_fcm_token}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropForeignKey('{{%fk-user_fcm_token-user_id}}', '{{%user_fcm_token}}');
        $this->dropIndex('{{%idx-user_fcm_token-user_id}}', '{{%user_fcm_token}}');
        $this->dropTable('{{%user_fcm_token}}');
    }
}
