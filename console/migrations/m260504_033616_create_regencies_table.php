<?php

use yii\db\Migration;

/**
 * Handles the creation of table `regencies`.
 * Has foreign keys to the tables:
 *
 * - `provinces`
 */
class m260504_033616_create_regencies_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('regencies', [
            'id' => $this->primaryKey(),
            'province_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
        ]);

        // creates index for column `province_id`
        $this->createIndex(
            'idx-regencies-province_id',
            'regencies',
            'province_id'
        );

        // add foreign key for table `provinces`
        $this->addForeignKey(
            'fk-regencies-province_id',
            'regencies',
            'province_id',
            'provinces',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `provinces`
        $this->dropForeignKey(
            'fk-regencies-province_id',
            'regencies'
        );

        // drops index for column `province_id`
        $this->dropIndex(
            'idx-regencies-province_id',
            'regencies'
        );

        $this->dropTable('regencies');
    }
}
