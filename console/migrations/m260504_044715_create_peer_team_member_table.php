<?php

use common\enums\ApprovalStatus;
use common\enums\TeamRole;
use yii\db\Migration;

/**
 * Handles the creation of table `peer_team_member`.
 * Has foreign keys to the tables:
 *
 * - `certification`
 * - `user`
 */
class m260504_044715_create_peer_team_member_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('peer_team_member', [
            'id' => $this->primaryKey(),
            'certification_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string()->notNull()->defaultValue(ApprovalStatus::PENDING),
            'role' => $this->string()->notNull()->defaultValue(TeamRole::MEMBER),
        ]);

        // creates index for column `certification_id`
        $this->createIndex(
            'idx-peer_team_member-certification_id',
            'peer_team_member',
            'certification_id'
        );

        // add foreign key for table `certification`
        $this->addForeignKey(
            'fk-peer_team_member-certification_id',
            'peer_team_member',
            'certification_id',
            'certification',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            'idx-peer_team_member-user_id',
            'peer_team_member',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-peer_team_member-user_id',
            'peer_team_member',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // peer_team_member (status)
        $this->addCheck(
            'chk-peer_team_member-status',
            'peer_team_member',
            "status IN ('" . implode("','", ApprovalStatus::values()) . "')"
        );

        // peer_team_member (role)
        $this->addCheck(
            'chk-peer_team_member-role',
            'peer_team_member',
            "role IN ('" . implode("','", TeamRole::values()) . "')"
        );

        // unique constraint: one user, one certification
        $this->createIndex(
            'uq-peer_team_member-certification-user',
            'peer_team_member',
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
            'uq-peer_team_member-certification-user',
            'peer_team_member',
        );

        // peer_team_member (status)
        $this->dropCheck('chk-peer_team_member-status', 'peer_team_member');

        // peer_team_member (role)
        $this->dropCheck('chk-peer_team_member-role', 'peer_team_member');

        // drops foreign key for table `certification`
        $this->dropForeignKey(
            'fk-peer_team_member-certification_id',
            'peer_team_member'
        );

        // drops index for column `certification_id`
        $this->dropIndex(
            'idx-peer_team_member-certification_id',
            'peer_team_member'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-peer_team_member-user_id',
            'peer_team_member'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-peer_team_member-user_id',
            'peer_team_member'
        );

        $this->dropTable('peer_team_member');
    }
}
