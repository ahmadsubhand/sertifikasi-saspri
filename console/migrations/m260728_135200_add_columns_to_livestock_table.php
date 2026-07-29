<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%livestock}}`.
 */
class m260728_135200_add_columns_to_livestock_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%livestock}}', 'first_price', $this->integer()->defaultValue(0));
        $this->addColumn('{{%livestock}}', 'breeding_investment', $this->integer()->defaultValue(0));
        
        $this->alterColumn('{{%livestock}}', 'eid', $this->string(32)->defaultValue(null));
        
        $this->createIndex(
            '{{%idx_livestock_eid_unique}}',
            '{{%livestock}}',
            'eid',
            true
        );
    }

    public function safeDown()
    {
        $this->dropIndex('{{%idx_livestock_eid_unique}}', '{{%livestock}}');
        $this->alterColumn('{{%livestock}}', 'eid', $this->string(32)->defaultValue(null));
        
        $this->dropColumn('{{%livestock}}', 'breeding_investment');
        $this->dropColumn('{{%livestock}}', 'first_price');
    }
}
