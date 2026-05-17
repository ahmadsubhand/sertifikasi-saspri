<?php

use common\enums\ApprovalStatus;
use common\enums\TeamRole;
use yii\db\Migration;

/**
 * Handles the creation of table `self_team_member`.
 * Has foreign keys to the tables:
 *
 * - `certification`
 * - `user`
 */
class m260504_044705_create_self_team_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('self_team_member', [
            'id' => $this->primaryKey(),
            'certification_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string()->notNull()->defaultValue(ApprovalStatus::PENDING),
            'role' => $this->string()->notNull()->defaultValue(TeamRole::MEMBER),
        ]);

        // creates index for column `certification_id`
        $this->createIndex(
            'idx-self_team_member-certification_id',
            'self_team_member',
            'certification_id'
        );

        // add foreign key for table `certification`
        $this->addForeignKey(
            'fk-self_team_member-certification_id',
            'self_team_member',
            'certification_id',
            'certification',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            'idx-self_team_member-user_id',
            'self_team_member',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-self_team_member-user_id',
            'self_team_member',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // self_team_member (status)
        $this->addCheck(
            'chk-self_team_member-status',
            'self_team_member',
            "status IN ('" . implode("','", ApprovalStatus::values()) . "')"
        );

        // self_team_member (role)
        $this->addCheck(
            'chk-self_team_member-role',
            'self_team_member',
            "role IN ('" . implode("','", TeamRole::values()) . "')"
        );

        // unique constraint: one user, one certification
        $this->createIndex(
            'uq-self_team_member-certification-user',
            'self_team_member',
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
            'uq-self_team_member-certification-user',
            'self_team_member',
        );

        // self_team_member (status)
        $this->dropCheck('chk-self_team_member-status', 'self_team_member');

        // self_team_member (role)
        $this->dropCheck('chk-self_team_member-role', 'self_team_member');

        // drops foreign key for table `certification`
        $this->dropForeignKey(
            'fk-self_team_member-certification_id',
            'self_team_member'
        );

        // drops index for column `certification_id`
        $this->dropIndex(
            'idx-self_team_member-certification_id',
            'self_team_member'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-self_team_member-user_id',
            'self_team_member'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-self_team_member-user_id',
            'self_team_member'
        );

        $this->dropTable('self_team_member');
    }
}
