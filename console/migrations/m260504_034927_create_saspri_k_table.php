<?php

use common\enums\ApprovalStatus;
use yii\db\Migration;

/**
 * Handles the creation of table `saspri_k`.
 * Has foreign keys to the tables:
 *
 * - `user`
 * - `districts`
 * - `user`
 */
class m260504_034927_create_saspri_k_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('saspri_k', [
            'id' => $this->primaryKey(),
            'coordinator_id' => $this->integer()->notNull()->unique(),
            // role wali sebenarnya bisa di dapat dari sini,
            // users.id === saspri_k.coordinator_id

            // Informasi SASPRI-K
            // Apa saja informasi yang opsional?
            'district_id' => $this->integer()->notNull(),
            'region_name' => $this->string()->notNull(),
            'address' => $this->string()->notNull(),
            'cooperative_name' => $this->string()->notNull(),
            'number_of_groups' => $this->integer()->notNull(),
            'number_of_active_members' => $this->integer()->notNull(),
            'livestock_type' => $this->string()->notNull(),
            'total_livestock_count' => $this->integer()->notNull(),
            'breeding_livestock_count' => $this->integer()->notNull(),
            'productive_heifer_count' => $this->integer()->notNull(),

            // Pendaftaran Wali / SASPRI-K
            'request_status' => $this->string()->notNull()->defaultValue(ApprovalStatus::PENDING),
            'request_rejection_reason' => $this->string(),

            // Pergantian Wali
            'change_status' => $this->string()->notNull()->defaultValue(ApprovalStatus::APPROVED),
            'new_coordinator_id' => $this->integer()->unique(),
            'change_request_reason' => $this->string(),
            'change_rejection_reason' => $this->string(),
        ]);

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-saspri_k-coordinator_id',
            'saspri_k',
            'coordinator_id',
            'user',
            'id',
            'CASCADE'
        );

        // creates index for column `district_id`
        $this->createIndex(
            'idx-saspri_k-district_id',
            'saspri_k',
            'district_id'
        );

        // add foreign key for table `district`
        $this->addForeignKey(
            'fk-saspri_k-district_id',
            'saspri_k',
            'district_id',
            'district',
            'id',
            'CASCADE'
        );

        // add foreign key for table `users`
        $this->addForeignKey(
            'fk-saspri_k-new_coordinator_id',
            'saspri_k',
            'new_coordinator_id',
            'user',
            'id',
            'CASCADE'
        );

        $this->addCheck(
            'chk-saspri_k-request_status',
            'saspri_k',
            "request_status IN ('" . implode("','", ApprovalStatus::values()) . "')"
        );

        $this->addCheck(
            'chk-saspri_k-change_status',
            'saspri_k',
            "change_status IN ('" . implode("','", ApprovalStatus::values()) . "')"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drop constraint for column `change_status`
        $this->dropCheck('chk-saspri_k-change_status', 'saspri_k');

        // drop constraint for column `request_status`
        $this->dropCheck('chk-saspri_k-request_status', 'saspri_k');

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-saspri_k-coordinator_id',
            'saspri_k'
        );

        // drops foreign key for table `district`
        $this->dropForeignKey(
            'fk-saspri_k-district_id',
            'saspri_k'
        );

        // drops index for column `district_id`
        $this->dropIndex(
            'idx-saspri_k-district_id',
            'saspri_k'
        );

        // drops foreign key for table `user`
        $this->dropForeignKey(
            'fk-saspri_k-new_coordinator_id',
            'saspri_k'
        );

        $this->dropTable('saspri_k');
    }
}
