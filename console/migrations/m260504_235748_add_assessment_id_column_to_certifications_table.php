<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `certifications`.
 * Has foreign keys to the tables:
 *
 * - `assessments`
 */
class m260504_235748_add_assessment_id_column_to_certifications_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('certifications', 'assessment_id', $this->integer()->notNull());

        // creates index for column `assessment_id`
        $this->createIndex(
            'idx-certifications-assessment_id',
            'certifications',
            'assessment_id'
        );

        // add foreign key for table `assessments`
        $this->addForeignKey(
            'fk-certifications-assessment_id',
            'certifications',
            'assessment_id',
            'assessments',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `assessments`
        $this->dropForeignKey(
            'fk-certifications-assessment_id',
            'certifications'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            'idx-certifications-assessment_id',
            'certifications'
        );

        $this->dropColumn('certifications', 'assessment_id');
    }
}
