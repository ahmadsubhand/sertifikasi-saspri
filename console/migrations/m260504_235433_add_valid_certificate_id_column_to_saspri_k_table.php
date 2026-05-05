<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `saspri_k`.
 * Has foreign keys to the tables:
 *
 * - `certifications`
 */
class m260504_235433_add_valid_certificate_id_column_to_saspri_k_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('saspri_k', 'valid_certificate_id', $this->integer()->notNull()->unique());

        // add foreign key for table `certifications`
        $this->addForeignKey(
            'fk-saspri_k-valid_certificate_id',
            'saspri_k',
            'valid_certificate_id',
            'certifications',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `certifications`
        $this->dropForeignKey(
            'fk-saspri_k-valid_certificate_id',
            'saspri_k'
        );

        $this->dropColumn('saspri_k', 'valid_certificate_id');
    }
}
