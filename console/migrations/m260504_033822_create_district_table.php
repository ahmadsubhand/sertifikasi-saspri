<?php

use yii\db\Migration;

/**
 * Handles the creation of table `district`.
 * Has foreign keys to the tables:
 *
 * - `regency`
 */
class m260504_033822_create_district_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('district', [
            'id' => $this->primaryKey(),
            'regency_id' => $this->integer()->notNull(),
            'code' => $this->string()->notNull(),
            'name' => $this->string()->notNull(),
        ]);

        // creates index for column `regency_id`
        $this->createIndex(
            'idx-district-regency_id',
            'district',
            'regency_id'
        );

        // add foreign key for table `regency`
        $this->addForeignKey(
            'fk-district-regency_id',
            'district',
            'regency_id',
            'regency',
            'id',
            'CASCADE'
        );
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        // drops foreign key for table `regency`
        $this->dropForeignKey(
            'fk-district-regency_id',
            'district'
        );

        // drops index for column `regency_id`
        $this->dropIndex(
            'idx-district-regency_id',
            'district'
        );

        $this->dropTable('district');
    }
}
