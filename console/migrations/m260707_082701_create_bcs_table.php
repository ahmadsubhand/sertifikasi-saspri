<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%bcs}}`.
 */
class m260707_082701_create_bcs_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%bcs}}', [
            'id' => $this->primaryKey(),
            'livestock_id' => $this->integer()->notNull(),
            'body_weight' => $this->double()->notNull(),
            'chest_size' => $this->double()->notNull(),
            'hips' => $this->double()->notNull(),
            'bcs_image' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // Membuat index untuk kolom `livestock_id`
        $this->createIndex(
            '{{%livestock_idx}}',
            '{{%bcs}}',
            'livestock_id'
        );

        // Menambahkan foreign key yang mengarah ke tabel `livestock`
        $this->addForeignKey(
            '{{%fk_livestock_bcs}}',
            '{{%bcs}}',
            'livestock_id',
            '{{%livestock}}',
            'id',
            'CASCADE', // Aksi saat data parent (livestock) dihapus
            'CASCADE'  // Aksi saat data parent (livestock) diupdate
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign key terlebih dahulu sebelum drop tabel/index
        $this->dropForeignKey(
            '{{%fk_livestock_bcs}}',
            '{{%bcs}}'
        );

        // Drop index
        $this->dropIndex(
            '{{%livestock_idx}}',
            '{{%bcs}}'
        );

        $this->dropTable('{{%bcs}}');
    }
}
