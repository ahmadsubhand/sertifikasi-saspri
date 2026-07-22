<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%user}}`.
 */
class m260707_085524_add_custom_fields_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Menambahkan kolom baru ke tabel user
        $this->addColumn('{{%user}}', 'gender', "ENUM('Laki-laki','Perempuan') CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci DEFAULT NULL AFTER `email`");
        $this->addColumn('{{%user}}', 'nik', $this->string(16)->defaultValue(null)->after('gender'));
        $this->addColumn('{{%user}}', 'birthdate', $this->date()->defaultValue(null)->after('nik'));
        $this->addColumn('{{%user}}', 'address', $this->string(255)->defaultValue(null)->after('birthdate'));
        
        // Kolom penanda apakah profil user sudah lengkap atau belum
        $this->addColumn('{{%user}}', 'is_completed', $this->smallInteger(1)->notNull()->defaultValue(0)->after('address'));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Menghapus kolom jika melakukan rollback migrasi
        $this->dropColumn('{{%user}}', 'is_completed');
        $this->dropColumn('{{%user}}', 'address');
        $this->dropColumn('{{%user}}', 'birthdate');
        $this->dropColumn('{{%user}}', 'nik');
        $this->dropColumn('{{%user}}', 'gender');
    }
}