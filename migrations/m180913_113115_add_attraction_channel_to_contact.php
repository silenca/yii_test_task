<?php

use yii\db\Migration;

class m180913_113115_add_attraction_channel_to_contact extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%contact}}','attraction_channel_id',$this->integer());
        $this->addForeignKey('contact_attraction_channel_fk','{{%contact}}','attraction_channel_id','{{%attraction_channel}}','id','SET NULL','CASCADE');
    }

    public function down()
    {
        $this->dropForeignKey('contact_attraction_channel_fk','{{%contact}}');
        $this->dropColumn('{{%contact}}','attraction_channel_id');
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
