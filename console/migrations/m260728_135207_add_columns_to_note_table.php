<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%note}}`.
 */
class m260728_135207_add_columns_to_note_table extends Migration
{
    public function safeUp()
    {
        $this->dropColumn('{{%note}}', 'feed_weight');
        $this->dropColumn('{{%note}}', 'vitamin');
        $this->dropColumn('{{%note}}', 'costs');

        $this->addColumn('{{%note}}', 'forage_weight', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'forage_costs', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'consentrate_weight', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'consentrate_costs', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'additive_weight', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'additive_costs', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'vaccine', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'vitamin', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'pregnancy_check', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'antibiotics', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'anthelmintic', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'insemination', $this->double()->defaultValue(0));
        $this->addColumn('{{%note}}', 'note_date', $this->dateTime()->defaultValue(null));
        
        $this->alterColumn('{{%note}}', 'livestock_cage', $this->string(255));

        // Membuat unique key untuk note_date
        $this->createIndex(
            '{{%note_date_UNIQUE}}',
            '{{%note}}',
            'note_date',
            true
        );
    }

    public function safeDown()
    {
        $this->dropIndex('{{%note_date_UNIQUE}}', '{{%note}}');
        
        $this->dropColumn('{{%note}}', 'note_date');
        $this->dropColumn('{{%note}}', 'insemination');
        $this->dropColumn('{{%note}}', 'anthelmintic');
        $this->dropColumn('{{%note}}', 'antibiotics');
        $this->dropColumn('{{%note}}', 'pregnancy_check');
        $this->dropColumn('{{%note}}', 'vitamin');
        $this->dropColumn('{{%note}}', 'vaccine');
        $this->dropColumn('{{%note}}', 'additive_costs');
        $this->dropColumn('{{%note}}', 'additive_weight');
        $this->dropColumn('{{%note}}', 'consentrate_costs');
        $this->dropColumn('{{%note}}', 'consentrate_weight');
        $this->dropColumn('{{%note}}', 'forage_costs');
        $this->dropColumn('{{%note}}', 'forage_weight');

        $this->addColumn('{{%note}}', 'feed_weight', $this->double()->notNull());
        $this->addColumn('{{%note}}', 'vitamin', $this->string(45));
        $this->addColumn('{{%note}}', 'costs', $this->double()->notNull());
    }
}
