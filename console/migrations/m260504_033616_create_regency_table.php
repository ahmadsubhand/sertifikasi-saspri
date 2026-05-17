<?php

use yii\db\Migration;

/**
 * Handles the creation of table `regency`.
 * Has foreign keys to the tables:
 *
 * - `province`
 */
class m260504_033616_create_regency_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('regency', [
            'id' => $this->primaryKey(),
            'province_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
        ]);

        // creates index for column `province_id`
        $this->createIndex(
            'idx-regency-province_id',
            'regency',
            'province_id'
        );

        // add foreign key for table `province`
        $this->addForeignKey(
            'fk-regency-province_id',
            'regency',
            'province_id',
            'province',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `province`
        $this->dropForeignKey(
            'fk-regency-province_id',
            'regency'
        );

        // drops index for column `province_id`
        $this->dropIndex(
            'idx-regency-province_id',
            'regency'
        );

        $this->dropTable('regency');
    }
}
