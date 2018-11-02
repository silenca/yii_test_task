<?php

use yii\db\Migration;

class m180927_112516_add_cols_config extends Migration
{
    public function up()
    {
        $this->addColumn('{{%user}}','cols_config',$this->text());
    }

    public function down()
    {
        $this->dropColumn('{{%user}}','cols_config');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
