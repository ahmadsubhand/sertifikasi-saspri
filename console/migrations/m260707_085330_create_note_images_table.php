<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%note_images}}`.
 */
class m260707_085330_create_note_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%note_images}}', [
            'id' => $this->primaryKey(),
            'note_id' => $this->integer()->defaultValue(null),
            'image_path' => $this->string(255)->defaultValue(null),
        ]);

        // Membuat index
        $this->createIndex(
            '{{%note_idx}}',
            '{{%note_images}}',
            'note_id'
        );

        // Menambahkan foreign key ke tabel note
        $this->addForeignKey(
            '{{%fk_note_images}}',
            '{{%note_images}}',
            'note_id',
            '{{%note}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key
        $this->dropForeignKey('{{%fk_note_images}}', '{{%note_images}}');
        
        // Drop index
        $this->dropIndex('{{%note_idx}}', '{{%note_images}}');
        
        // Drop table
        $this->dropTable('{{%note_images}}');
    }
}
