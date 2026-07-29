<?php

use yii\db\Migration;

/**
 * Handles the creation of table `{{%price_list}}`.
 */
class m260728_135214_create_price_list_table extends Migration
{
    public function safeUp()
    {
        $this->createTable('{{%price_list}}', [
            'user_id' => $this->integer()->notNull(),
            'land' => $this->integer()->defaultValue(0),
            'employee' => $this->integer()->defaultValue(0),
            'wage' => $this->integer()->defaultValue(0),
            'livestock_per_employee' => $this->integer()->defaultValue(1),
            'inflation' => $this->integer()->defaultValue(0),
            'margin' => $this->integer()->defaultValue(0),
            'updated_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP'),
            'created_at' => $this->timestamp()->notNull()->defaultExpression('CURRENT_TIMESTAMP'),
            'electricity_water' => $this->integer()->notNull()->defaultValue(0),
            'PRIMARY KEY (user_id)', // Set PK
        ]);

        $this->addForeignKey(
            '{{%price_list_ibfk_1}}',
            '{{%price_list}}',
            'user_id',
            '{{%user}}',
            'id',
            'CASCADE',
            'CASCADE'
        );
    }

    public function safeDown()
    {
        $this->dropForeignKey('{{%price_list_ibfk_1}}', '{{%price_list}}');
        $this->dropTable('{{%price_list}}');
    }
}
