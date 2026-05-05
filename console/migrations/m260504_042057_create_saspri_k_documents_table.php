<?php

use yii\db\Migration;

/**
 * Handles the creation of table `saspri_k_documents`.
 * Has foreign keys to the tables:
 *
 * - `saspri_k`
 */
class m260504_042057_create_saspri_k_documents_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('saspri_k_documents', [
            'id' => $this->primaryKey(),
            'url' => $this->string()->notNull(),
            // Apakah KTP termasuk ke berkas pribadi?
            'type' => $this->string()->notNull(),
            'saspri_k_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `saspri_k_id`
        $this->createIndex(
            'idx-saspri_k_documents-saspri_k_id',
            'saspri_k_documents',
            'saspri_k_id'
        );

        // add foreign key for table `saspri_k`
        $this->addForeignKey(
            'fk-saspri_k_documents-saspri_k_id',
            'saspri_k_documents',
            'saspri_k_id',
            'saspri_k',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `saspri_k`
        $this->dropForeignKey(
            'fk-saspri_k_documents-saspri_k_id',
            'saspri_k_documents'
        );

        // drops index for column `saspri_k_id`
        $this->dropIndex(
            'idx-saspri_k_documents-saspri_k_id',
            'saspri_k_documents'
        );

        $this->dropTable('saspri_k_documents');
    }
}
