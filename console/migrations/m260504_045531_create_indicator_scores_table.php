<?php

use common\enums\IndicatorStatus;
use yii\db\Migration;

/**
 * Handles the creation of table `indicator_scores`.
 * Has foreign keys to the tables:
 *
 * - `indicators`
 * - `certifications`
 */
class m260504_045531_create_indicator_scores_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicator_scores', [
            'id' => $this->primaryKey(),
            'indicator_id' => $this->integer()->notNull(),
            'certification_id' => $this->integer()->notNull(),
            'self_team_score' => $this->integer(),
            'peer_team_score' => $this->integer(), // need interval?
            'status' => $this->string(),
            'evidence_url' => $this->string(),
        ]);

        // creates index for column `indicator_id`
        $this->createIndex(
            'idx-indicator_scores-indicator_id',
            'indicator_scores',
            'indicator_id'
        );

        // add foreign key for table `indicators`
        $this->addForeignKey(
            'fk-indicator_scores-indicator_id',
            'indicator_scores',
            'indicator_id',
            'indicators',
            'id',
            'CASCADE'
        );

        // creates index for column `certification_id`
        $this->createIndex(
            'idx-indicator_scores-certification_id',
            'indicator_scores',
            'certification_id'
        );

        // add foreign key for table `certifications`
        $this->addForeignKey(
            'fk-indicator_scores-certification_id',
            'indicator_scores',
            'certification_id',
            'certifications',
            'id',
            'CASCADE'
        );

        $this->addCheck(
            'chk-indicator_scores-status',
            'indicator_scores',
            "status IN ('" . implode("','", IndicatorStatus::values()) . "')"
        );

        $this->createIndex(
            'uq-indicator_scores-certification',
            'indicator_scores',
            ['indicator_id', 'certification_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // unique constraint: one score on one indicator and one certification
        $this->dropIndex(
            'uq-indicator_scores-certification',
            'indicator_scores'
        );

        $this->dropCheck('chk-indicator_scores-status', 'indicator_scores');

        // drops foreign key for table `indicators`
        $this->dropForeignKey(
            'fk-indicator_scores-indicator_id',
            'indicator_scores'
        );

        // drops index for column `indicator_id`
        $this->dropIndex(
            'idx-indicator_scores-indicator_id',
            'indicator_scores'
        );

        // drops foreign key for table `certifications`
        $this->dropForeignKey(
            'fk-indicator_scores-certification_id',
            'indicator_scores'
        );

        // drops index for column `certification_id`
        $this->dropIndex(
            'idx-indicator_scores-certification_id',
            'indicator_scores'
        );

        $this->dropTable('indicator_scores');
    }
}
