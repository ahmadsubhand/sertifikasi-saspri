<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%cow_family_tree}}`.
 */
class m260707_084748_create_cow_family_tree_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('{{%cow_family_tree}}', [
            'id' => $this->primaryKey(),
            'main_cow_id' => $this->integer()->notNull(),
            'father_id' => $this->integer()->defaultValue(null),
            'mother_id' => $this->integer()->defaultValue(null),
            
            // Menggunakan tipe data JSON
            'partners' => $this->json()->defaultValue(null),
            'children' => $this->json()->defaultValue(null),
            
            // Perhatikan bahwa di SQL Anda, ini menggunakan tipe DATETIME (bukan TIMESTAMP)
            'created_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP'),
            'updated_at' => $this->dateTime()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
        ]);

        // --- PEMBUATAN INDEX ---
        // Index Unik untuk main_cow_id
        $this->createIndex(
            '{{%main_cow_id_unique}}',
            '{{%cow_family_tree}}',
            'main_cow_id',
            true
        );

        // Index biasa untuk foreign key lainnya
        $this->createIndex(
            '{{%idx-cow_family_tree-father_id}}',
            '{{%cow_family_tree}}',
            'father_id'
        );

        $this->createIndex(
            '{{%idx-cow_family_tree-mother_id}}',
            '{{%cow_family_tree}}',
            'mother_id'
        );

        // --- PEMBUATAN FOREIGN KEY ---
        // FK main_cow_id -> livestock
        $this->addForeignKey(
            '{{%fk_main}}',
            '{{%cow_family_tree}}',
            'main_cow_id',
            '{{%livestock}}',
            'id'
            // Pada SQL asli tidak ada ON DELETE/UPDATE yang didefinisikan secara eksplisit
            // jadi kita biarkan menggunakan default (RESTRICT/NO ACTION)
        );

        // FK father_id -> livestock
        $this->addForeignKey(
            '{{%fk_father}}',
            '{{%cow_family_tree}}',
            'father_id',
            '{{%livestock}}',
            'id'
        );

        // FK mother_id -> livestock
        $this->addForeignKey(
            '{{%fk_mother}}',
            '{{%cow_family_tree}}',
            'mother_id',
            '{{%livestock}}',
            'id'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // Drop foreign keys
        $this->dropForeignKey('{{%fk_main}}', '{{%cow_family_tree}}');
        $this->dropForeignKey('{{%fk_father}}', '{{%cow_family_tree}}');
        $this->dropForeignKey('{{%fk_mother}}', '{{%cow_family_tree}}');

        // Drop indexes
        $this->dropIndex('{{%main_cow_id_unique}}', '{{%cow_family_tree}}');
        $this->dropIndex('{{%idx-cow_family_tree-father_id}}', '{{%cow_family_tree}}');
        $this->dropIndex('{{%idx-cow_family_tree-mother_id}}', '{{%cow_family_tree}}');

        // Drop table
        $this->dropTable('{{%cow_family_tree}}');
    }
}
