<?php

use yii\db\Migration;

class m181102_142327_add_link_with_column__contacts_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%contact}}', 'link_with', $this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%contact}}', 'link_with');
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
