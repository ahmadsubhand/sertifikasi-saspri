<?php

use yii\db\Migration;

/**
 * Handles adding columns to table `{{%cage}}`.
 */
class m260728_135152_add_columns_to_cage_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%cage}}', 'investasi_kandang', $this->integer()->defaultValue(0));
        $this->addColumn('{{%cage}}', 'umur_ekonomis', $this->double()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%cage}}', 'umur_ekonomis');
        $this->dropColumn('{{%cage}}', 'investasi_kandang');
    }
}
