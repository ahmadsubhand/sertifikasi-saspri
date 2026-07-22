<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cage}}`.
 */
class m260707_082323_create_cage_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cage}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->defaultValue(null),
            'name' => $this->string(50)->notNull(),
            'location' => $this->string(255)->notNull(),
            'capacity' => $this->integer()->notNull()->defaultValue(0),
            'description' => $this->string(255)->notNull(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Membuat index untuk kolom `user_id`
        $this->createIndex(
            '{{%user_idx}}',
            '{{%cage}}',
            'user_id'
        );

        // Menambahkan foreign key yang mengarah ke tabel `user`
        $this->addForeignKey(
            '{{%fk_user_cage}}',
            '{{%cage}}',
            'user_id',
            '{{%user}}',
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
        // Drop foreign key terlebih dahulu
        $this->dropForeignKey(
            '{{%fk_user_cage}}',
            '{{%cage}}'
        );

        // Drop index
        $this->dropIndex(
            '{{%user_idx}}',
            '{{%cage}}'
        );

        $this->dropTable('{{%cage}}');
    }
}
