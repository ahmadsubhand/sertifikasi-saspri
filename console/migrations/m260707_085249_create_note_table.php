<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%note}}`.
 */
class m260707_085249_create_note_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%note}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->defaultValue(null),
            'livestock_name' => $this->string(255)->notNull(),
            'livestock_id' => $this->integer()->defaultValue(null),
            'livestock_vid' => $this->string(10)->notNull(),
            'livestock_cage' => $this->string(10)->notNull(),
            'location' => $this->string(255)->notNull(),
            'livestock_feed' => $this->string(255)->notNull(),
            'feed_weight' => $this->double()->notNull(),
            'vitamin' => $this->string(45)->defaultValue(null),
            'costs' => $this->double()->notNull(),
            'details' => $this->text(),
            'documentation' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Membuat index
        $this->createIndex('{{%livestock_idx}}', '{{%note}}', 'livestock_id');
        $this->createIndex('{{%user_idx}}', '{{%note}}', 'user_id');
        $this->createIndex('{{%livestock_vid_idx}}', '{{%note}}', 'livestock_vid');

        // Menambahkan foreign key
        $this->addForeignKey('{{%fk_livestock_id}}', '{{%note}}', 'livestock_id', '{{%livestock}}', 'id');
        $this->addForeignKey('{{%fk_livestock_vid}}', '{{%note}}', 'livestock_vid', '{{%livestock}}', 'vid');
        $this->addForeignKey('{{%fk_user_note}}', '{{%note}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys terlebih dahulu
        $this->dropForeignKey('{{%fk_livestock_id}}', '{{%note}}');
        $this->dropForeignKey('{{%fk_livestock_vid}}', '{{%note}}');
        $this->dropForeignKey('{{%fk_user_note}}', '{{%note}}');

        // Drop indexes
        $this->dropIndex('{{%livestock_idx}}', '{{%note}}');
        $this->dropIndex('{{%user_idx}}', '{{%note}}');
        $this->dropIndex('{{%livestock_vid_idx}}', '{{%note}}');

        // Terakhir, drop table
        $this->dropTable('{{%note}}');
    }
}
