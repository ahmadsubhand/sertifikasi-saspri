<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%livestock}}`.
 */
class m260707_082508_create_livestock_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%livestock}}', [
            'id' => $this->primaryKey(),
            'user_id' => $this->integer()->defaultValue(null),
            'eid' => $this->bigInteger()->defaultValue(null),
            'vid' => $this->string(10)->notNull(),
            'cage_id' => $this->integer()->notNull(),
            'father_id' => $this->integer()->defaultValue(null),
            'mother_id' => $this->integer()->defaultValue(null),
            'partner_id' => $this->integer()->defaultValue(null),
            'name' => $this->string(255)->notNull(),
            'birthdate' => $this->date()->notNull(),
            'age' => $this->integer()->notNull(),
            
            // Penggunaan ENUM langsung (Spesifik MySQL)
            'gender' => "ENUM('Jantan','Betina') NOT NULL",
            'type_of_livestock' => "ENUM('Kambing','Sapi') NOT NULL",
            'breed_of_livestock' => "ENUM('Madura','Bali','Limousin','Brahman') NOT NULL",
            'purpose' => "ENUM('Indukan','Penggemukan','Tabungan','Belum Tahu') NOT NULL",
            'maintenance' => "ENUM('Kandang','Gembala','Campuran') NOT NULL",
            'source' => "ENUM('Sejak Lahir','Bantuan Pemerintah','Beli','Beli dari Luar Kelompok','Beli dari Dalam Kelompok','Inseminasi Buatan','Kawin Alam','Tidak Tahu') NOT NULL",
            'ownership_status' => "ENUM('Sendiri','Kelompok','Titipan') NOT NULL",
            'reproduction' => "ENUM('Tidak Bunting','Bunting < 1 bulan','Bunting 1 bulan','Bunting 2 bulan','Bunting 3 bulan','Bunting 4 bulan','Bunting 5 bulan','Bunting 6 bulan','Bunting 7 bulan','Bunting 8 bulan','Bunting 9 bulan','Bunting 10 bulan','Bunting 11 bulan','Bunting > 11 bulan') NOT NULL",
            
            'chest_size' => $this->double()->notNull(),
            'body_weight' => $this->double()->notNull(),
            'health' => "ENUM('Sehat','Sakit','Mati') NOT NULL",
            'livestock_image' => $this->text(),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // --- PEMBUATAN INDEX ---
        // Index Unik
        $this->createIndex('{{%vid_unique}}', '{{%livestock}}', 'vid', true);
        $this->createIndex('{{%eid_unique}}', '{{%livestock}}', 'eid', true);

        // Index Biasa
        $this->createIndex('{{%cage_idx}}', '{{%livestock}}', 'cage_id');
        $this->createIndex('{{%user_idx}}', '{{%livestock}}', 'user_id');
        $this->createIndex('{{%fk-livestock-father_id_idx}}', '{{%livestock}}', 'father_id');
        $this->createIndex('{{%fk-livestock-mother_id_idx}}', '{{%livestock}}', 'mother_id');
        $this->createIndex('{{%fk-livestock-partner_id_idx}}', '{{%livestock}}', 'partner_id');

        // --- PEMBUATAN FOREIGN KEY ---
        // Self-referencing FK untuk Silsilah & Pasangan
        $this->addForeignKey('{{%fk-livestock-father_id}}', '{{%livestock}}', 'father_id', '{{%livestock}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('{{%fk-livestock-mother_id}}', '{{%livestock}}', 'mother_id', '{{%livestock}}', 'id', 'SET NULL', 'CASCADE');
        $this->addForeignKey('{{%fk-livestock-partner_id}}', '{{%livestock}}', 'partner_id', '{{%livestock}}', 'id', 'SET NULL', 'CASCADE');

        // FK ke tabel Relasional lainnya
        $this->addForeignKey('{{%fk_cage_livestock}}', '{{%livestock}}', 'cage_id', '{{%cage}}', 'id');
        $this->addForeignKey('{{%fk_user_livestock}}', '{{%livestock}}', 'user_id', '{{%user}}', 'id');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop semua Foreign Key terlebih dahulu
        $this->dropForeignKey('{{%fk-livestock-father_id}}', '{{%livestock}}');
        $this->dropForeignKey('{{%fk-livestock-mother_id}}', '{{%livestock}}');
        $this->dropForeignKey('{{%fk-livestock-partner_id}}', '{{%livestock}}');
        $this->dropForeignKey('{{%fk_cage_livestock}}', '{{%livestock}}');
        $this->dropForeignKey('{{%fk_user_livestock}}', '{{%livestock}}');

        // Drop semua Index
        $this->dropIndex('{{%vid_unique}}', '{{%livestock}}');
        $this->dropIndex('{{%eid_unique}}', '{{%livestock}}');
        $this->dropIndex('{{%cage_idx}}', '{{%livestock}}');
        $this->dropIndex('{{%user_idx}}', '{{%livestock}}');
        $this->dropIndex('{{%fk-livestock-father_id_idx}}', '{{%livestock}}');
        $this->dropIndex('{{%fk-livestock-mother_id_idx}}', '{{%livestock}}');
        $this->dropIndex('{{%fk-livestock-partner_id_idx}}', '{{%livestock}}');

        $this->dropTable('{{%livestock}}');
    }
}
