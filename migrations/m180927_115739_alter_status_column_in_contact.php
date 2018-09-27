<?php

use yii\db\Migration;

class m180927_115739_alter_status_column_in_contact extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%contact}}','status',$this->string());

    }

    public function down()
    {
        $this->dropColumn('{{%contact}}','status');
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
