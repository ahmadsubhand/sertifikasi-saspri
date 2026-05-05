<?php

use yii\db\Migration;

/**
 * Handles the creation of table `assessment_indicator_relations`.
 * Has foreign keys to the tables:
 *
 * - `assessments`
 * - `indicators`
 */
class m260504_234721_create_assessment_indicator_relations_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('assessment_indicator_relations', [
            'id' => $this->primaryKey(),
            'assessment_id' => $this->integer()->notNull(),
            'indicator_id' => $this->integer()->notNull(),
        ]);

        // creates index for column `assessment_id`
        $this->createIndex(
            'idx-assessment_indicator_relations-assessment_id',
            'assessment_indicator_relations',
            'assessment_id'
        );

        // add foreign key for table `assessments`
        $this->addForeignKey(
            'fk-assessment_indicator_relations-assessment_id',
            'assessment_indicator_relations',
            'assessment_id',
            'assessments',
            'id',
            'CASCADE'
        );

        // creates index for column `indicator_id`
        $this->createIndex(
            'idx-assessment_indicator_relations-indicator_id',
            'assessment_indicator_relations',
            'indicator_id'
        );

        // add foreign key for table `indicators`
        $this->addForeignKey(
            'fk-assessment_indicator_relations-indicator_id',
            'assessment_indicator_relations',
            'indicator_id',
            'indicators',
            'id',
            'CASCADE'
        );

        $this->createIndex(
            'uq-assessment-indicator',
            'assessment_indicator_relations',
            ['assessment_id', 'indicator_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'uq-assessment-indicator',
            'assessment_indicator_relations',
        );

        // drops foreign key for table `assessments`
        $this->dropForeignKey(
            'fk-assessment_indicator_relations-assessment_id',
            'assessment_indicator_relations'
        );

        // drops index for column `assessment_id`
        $this->dropIndex(
            'idx-assessment_indicator_relations-assessment_id',
            'assessment_indicator_relations'
        );

        // drops foreign key for table `indicators`
        $this->dropForeignKey(
            'fk-assessment_indicator_relations-indicator_id',
            'assessment_indicator_relations'
        );

        // drops index for column `indicator_id`
        $this->dropIndex(
            'idx-assessment_indicator_relations-indicator_id',
            'assessment_indicator_relations'
        );

        $this->dropTable('assessment_indicator_relations');
    }
}
