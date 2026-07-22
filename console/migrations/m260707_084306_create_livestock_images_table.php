<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%livestock_images}}`.
 */
class m260707_084306_create_livestock_images_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%livestock_images}}', [
            'id' => $this->primaryKey(),
            'livestock_id' => $this->integer()->defaultValue(null),
            'image_path' => $this->string(255)->defaultValue(null),
        ]);

        // Membuat index untuk livestock_id
        $this->createIndex(
            '{{%livestock_idx}}',
            '{{%livestock_images}}',
            'livestock_id'
        );

        // Menambahkan foreign key
        $this->addForeignKey(
            '{{%fk_livestock_images}}',
            '{{%livestock_images}}',
            'livestock_id',
            '{{%livestock}}',
            'id',
            'CASCADE', // ON DELETE CASCADE
            'CASCADE'  // ON UPDATE CASCADE
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key terlebih dahulu
        $this->dropForeignKey('{{%fk_livestock_images}}', '{{%livestock_images}}');

        // Drop index
        $this->dropIndex('{{%livestock_idx}}', '{{%livestock_images}}');

        // Drop table
        $this->dropTable('{{%livestock_images}}');
    }
}
