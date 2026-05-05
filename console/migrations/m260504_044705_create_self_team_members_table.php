<?php

use common\enums\ApprovalStatus;
use common\enums\TeamRole;
use yii\db\Migration;

/**
 * Handles the creation of table `self_team_members`.
 * Has foreign keys to the tables:
 *
 * - `certifications`
 * - `user`
 */
class m260504_044705_create_self_team_members_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('self_team_members', [
            'id' => $this->primaryKey(),
            'certification_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string()->notNull(),
            'role' => $this->string()->notNull(),
        ]);

        // creates index for column `certification_id`
        $this->createIndex(
            'idx-self_team_members-certification_id',
            'self_team_members',
            'certification_id'
        );

        // add foreign key for table `certifications`
        $this->addForeignKey(
            'fk-self_team_members-certification_id',
            'self_team_members',
            'certification_id',
            'certifications',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            'idx-self_team_members-user_id',
            'self_team_members',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-self_team_members-user_id',
            'self_team_members',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // self_team_members (status)
        $this->addCheck(
            'chk-self_team_members-status',
            'self_team_members',
            "status IN ('" . implode("','", ApprovalStatus::values()) . "')"
        );

        // self_team_members (role)
        $this->addCheck(
            'chk-self_team_members-role',
            'self_team_members',
            "role IN ('" . implode("','", TeamRole::values()) . "')"
        );

        // unique constraint: one user, one certification
        $this->createIndex(
            'uq-self_team_members-certification-user',
            'self_team_members',
            ['certification_id', 'user_id'],
            true
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropIndex(
            'uq-self_team_members-certification-user',
            'self_team_members',
        );

        // self_team_members (status)
        $this->dropCheck('chk-self_team_members-status', 'self_team_members');

        // self_team_members (role)
        $this->dropCheck('chk-self_team_members-role', 'self_team_members');

        // drops foreign key for table `certifications`
        $this->dropForeignKey(
            'fk-self_team_members-certification_id',
            'self_team_members'
        );

        // drops index for column `certification_id`
        $this->dropIndex(
            'idx-self_team_members-certification_id',
            'self_team_members'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-self_team_members-user_id',
            'self_team_members'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-self_team_members-user_id',
            'self_team_members'
        );

        $this->dropTable('self_team_members');
    }
}
