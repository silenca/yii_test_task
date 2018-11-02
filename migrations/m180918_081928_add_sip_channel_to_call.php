<?php

use yii\db\Migration;

class m180918_081928_add_sip_channel_to_call extends Migration
{
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%call}}','sip_channel_id','integer');
        $this->addForeignKey('call_sip_channel_fk','{{%call}}','sip_channel_id','{{%sip_channel}}','id','SET NULL','CASCADE');
    }

    public function safeDown()
    {
        $this->dropForeignKey('call_sip_channel_fk','{{%call}}');
        $this->dropColumn('{{%call}}','sip_channel_id');
    }

}
