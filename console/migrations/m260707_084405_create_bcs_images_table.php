<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bcs_images}}`.
 */
class m260707_084405_create_bcs_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bcs_images}}', [
            'id' => $this->primaryKey(),
            'bcs_id' => $this->integer()->defaultValue(null),
            'image_path' => $this->string(255)->defaultValue(null),
        ]);

        // Membuat index untuk bcs_id
        $this->createIndex(
            '{{%bcs_idx}}',
            '{{%bcs_images}}',
            'bcs_id'
        );

        // Menambahkan foreign key ke tabel bcs
        $this->addForeignKey(
            '{{%fk_bcs_images}}',
            '{{%bcs_images}}',
            'bcs_id',
            '{{%bcs}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key terlebih dahulu
        $this->dropForeignKey('{{%fk_bcs_images}}', '{{%bcs_images}}');

        // Drop index
        $this->dropIndex('{{%bcs_idx}}', '{{%bcs_images}}');

        // Drop table
        $this->dropTable('{{%bcs_images}}');
    }
}
