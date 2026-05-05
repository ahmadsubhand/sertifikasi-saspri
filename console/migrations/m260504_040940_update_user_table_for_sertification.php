<?php

use common\enums\UserRole;
use yii\db\Migration;

/**
 * Handles the creation of table `user`.
 * Has foreign keys to the tables:
 *
 * - `saspri_k`
 */
class m260504_040940_update_user_table_for_sertification extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        // Add column
        $this->addColumn('user', 'saspri_k_id', $this->integer());
        $this->addColumn('user', 'phone_number', $this->string());
        $this->addColumn('user', 'role', $this->string()->notNull()->defaultValue(UserRole::USER));

        // creates index for column `saspri_k_id`
        $this->createIndex(
            'idx-user-saspri_k_id',
            'user',
            'saspri_k_id'
        );

        // add foreign key for table `saspri_k`
        $this->addForeignKey(
            'fk-user-saspri_k_id',
            'user',
            'saspri_k_id',
            'saspri_k',
            'id',
            'SET NULL',
            'CASCADE'
        );

        $this->addCheck(
            'chk-user-role',
            'user',
            "role IN ('" . implode("','", UserRole::values()) . "')"
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropCheck('chk-user-role', 'user');

        // drops foreign key for table `saspri_k`
        $this->dropForeignKey(
            'fk-user-saspri_k_id',
            'user'
        );

        $this->dropIndex(
            'idx-user-saspri_k_id',
            'user',
        );

        $this->dropColumn('user', 'saspri_k_id');
        $this->dropColumn('user', 'phone_number');
        $this->dropColumn('user', 'role');
    }
}
