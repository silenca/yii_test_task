<?php

use yii\db\Migration;

class m181001_140703_add_birthday_to_contact extends Migration
{
    public function up()
    {
        $this->addColumn('{{%contact}}', 'birthday', $this->date());
    }

    public function down()
    {
        $this->dropColumn('{{%contact}}', 'birthday');
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
