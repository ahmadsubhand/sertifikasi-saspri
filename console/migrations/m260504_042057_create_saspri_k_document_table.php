<?php

use yii\db\Migration;

/**
 * Handles the creation of table `saspri_k_document`.
 * Has foreign keys to the tables:
 *
 * - `saspri_k`
 */
class m260504_042057_create_saspri_k_document_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('saspri_k_document', [
            'id' => $this->primaryKey(),
            'url' => $this->string()->notNull(),
            // Apakah KTP termasuk ke berkas pribadi?
            'type' => $this->string()->notNull(),
            'saspri_k_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `saspri_k_id`
        $this->createIndex(
            'idx-saspri_k_document-saspri_k_id',
            'saspri_k_document',
            'saspri_k_id'
        );

        // add foreign key for table `saspri_k`
        $this->addForeignKey(
            'fk-saspri_k_document-saspri_k_id',
            'saspri_k_document',
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
            'fk-saspri_k_document-saspri_k_id',
            'saspri_k_document'
        );

        // drops index for column `saspri_k_id`
        $this->dropIndex(
            'idx-saspri_k_document-saspri_k_id',
            'saspri_k_document'
        );

        $this->dropTable('saspri_k_document');
    }
}
