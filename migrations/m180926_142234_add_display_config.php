<?php

use yii\db\Migration;

class m180926_142234_add_display_config extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%user}}','filter_config',$this->text());
    }

    public function safeDown()
    {
        $this->dropColumn('{{%user}}','filter_config');
    }
}
