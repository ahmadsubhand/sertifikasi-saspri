<?php

use common\enums\IndicatorStatus;
use yii\db\Migration;

/**
 * Handles the creation of table `indicator_score`.
 * Has foreign keys to the tables:
 *
 * - `indicator`
 * - `certification`
 */
class m260504_045531_create_indicator_score_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('indicator_score', [
            'id' => $this->primaryKey(),
            'indicator_id' => $this->integer()->notNull(),
            'certification_id' => $this->integer()->notNull(),
            'self_team_score' => $this->integer(),
            'peer_team_score' => $this->integer(), // need interval?
            'status' => $this->string(),
            'final_score' => $this->integer(),
            'evidence_url' => $this->string(),
        ]);

        // creates index for column `indicator_id`
        $this->createIndex(
            'idx-indicator_score-indicator_id',
            'indicator_score',
            'indicator_id'
        );

        // add foreign key for table `indicator`
        $this->addForeignKey(
            'fk-indicator_score-indicator_id',
            'indicator_score',
            'indicator_id',
            'indicator',
            'id',
            'CASCADE'
        );

        // creates index for column `certification_id`
        $this->createIndex(
            'idx-indicator_score-certification_id',
            'indicator_score',
            'certification_id'
        );

        // add foreign key for table `certification`
        $this->addForeignKey(
            'fk-indicator_score-certification_id',
            'indicator_score',
            'certification_id',
            'certification',
            'id',
            'CASCADE'
        );

        $this->addCheck(
            'chk-indicator_score-status',
            'indicator_score',
            "status IN ('" . implode("','", IndicatorStatus::values()) . "')"
        );

        $this->createIndex(
            'uq-indicator_score-certification',
            'indicator_score',
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
            'uq-indicator_score-certification',
            'indicator_score'
        );

        $this->dropCheck('chk-indicator_score-status', 'indicator_score');

        // drops foreign key for table `indicator`
        $this->dropForeignKey(
            'fk-indicator_score-indicator_id',
            'indicator_score'
        );

        // drops index for column `indicator_id`
        $this->dropIndex(
            'idx-indicator_score-indicator_id',
            'indicator_score'
        );

        // drops foreign key for table `certification`
        $this->dropForeignKey(
            'fk-indicator_score-certification_id',
            'indicator_score'
        );

        // drops index for column `certification_id`
        $this->dropIndex(
            'idx-indicator_score-certification_id',
            'indicator_score'
        );

        $this->dropTable('indicator_score');
    }
}
