<?php

use yii\db\Migration;

/**
 * Handles the creation of table `districts`.
 * Has foreign keys to the tables:
 *
 * - `regencies`
 */
class m260504_033822_create_districts_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('districts', [
            'id' => $this->primaryKey(),
            'regency_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
        ]);

        // creates index for column `regency_id`
        $this->createIndex(
            'idx-districts-regency_id',
            'districts',
            'regency_id'
        );

        // add foreign key for table `regencies`
        $this->addForeignKey(
            'fk-districts-regency_id',
            'districts',
            'regency_id',
            'regencies',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `regencies`
        $this->dropForeignKey(
            'fk-districts-regency_id',
            'districts'
        );

        // drops index for column `regency_id`
        $this->dropIndex(
            'idx-districts-regency_id',
            'districts'
        );

        $this->dropTable('districts');
    }
}
