<?php

use common\enums\ApprovalStatus;
use common\enums\TeamRole;
use yii\db\Migration;

/**
 * Handles the creation of table `peer_team_members`.
 * Has foreign keys to the tables:
 *
 * - `certifications`
 * - `user`
 */
class m260504_044715_create_peer_team_members_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('peer_team_members', [
            'id' => $this->primaryKey(),
            'certification_id' => $this->integer()->notNull(),
            'user_id' => $this->integer()->notNull(),
            'status' => $this->string()->notNull()->defaultValue(ApprovalStatus::PENDING),
            'role' => $this->string()->notNull()->defaultValue(TeamRole::MEMBER),
        ]);

        // creates index for column `certification_id`
        $this->createIndex(
            'idx-peer_team_members-certification_id',
            'peer_team_members',
            'certification_id'
        );

        // add foreign key for table `certifications`
        $this->addForeignKey(
            'fk-peer_team_members-certification_id',
            'peer_team_members',
            'certification_id',
            'certifications',
            'id',
            'CASCADE'
        );

        // creates index for column `user_id`
        $this->createIndex(
            'idx-peer_team_members-user_id',
            'peer_team_members',
            'user_id'
        );

        // add foreign key for table `user`
        $this->addForeignKey(
            'fk-peer_team_members-user_id',
            'peer_team_members',
            'user_id',
            'user',
            'id',
            'CASCADE'
        );

        // peer_team_members (status)
        $this->addCheck(
            'chk-peer_team_members-status',
            'peer_team_members',
            "status IN ('" . implode("','", ApprovalStatus::values()) . "')"
        );

        // peer_team_members (role)
        $this->addCheck(
            'chk-peer_team_members-role',
            'peer_team_members',
            "role IN ('" . implode("','", TeamRole::values()) . "')"
        );

        // unique constraint: one user, one certification
        $this->createIndex(
            'uq-peer_team_members-certification-user',
            'peer_team_members',
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
            'uq-peer_team_members-certification-user',
            'peer_team_members',
        );
        
        // peer_team_members (status)
        $this->dropCheck('chk-peer_team_members-status', 'peer_team_members');

        // peer_team_members (role)
        $this->dropCheck('chk-peer_team_members-role', 'peer_team_members');

        // drops foreign key for table `certifications`
        $this->dropForeignKey(
            'fk-peer_team_members-certification_id',
            'peer_team_members'
        );

        // drops index for column `certification_id`
        $this->dropIndex(
            'idx-peer_team_members-certification_id',
            'peer_team_members'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-peer_team_members-user_id',
            'peer_team_members'
        );

        // drops index for column `user_id`
        $this->dropIndex(
            'idx-peer_team_members-user_id',
            'peer_team_members'
        );

        $this->dropTable('peer_team_members');
    }
}
