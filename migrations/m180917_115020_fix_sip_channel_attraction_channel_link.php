<?php

use yii\db\Migration;

class m180917_115020_fix_sip_channel_attraction_channel_link extends Migration
{


    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->dropForeignKey('attraction_channel_sip_channel_fk','{{%attraction_channel}}');
        $this->dropColumn('{{%attraction_channel}}','sip_channel_id');
        $this->addColumn('{{%sip_channel}}','attraction_channel_id','integer');
        $this->addForeignKey('sip_channel_attraction_channel_fk','{{%sip_channel}}','attraction_channel_id'
            ,'{{%attraction_channel}}','id'
            ,'SET NULL','CASCADE');
    }

    public function safeDown()
    {
        $this->addColumn('{{%attraction_channel}}','sip_channel_id','integer');
        $this->addForeignKey('attraction_channel_sip_channel_fk','{{%attraction_channel}}','sip_channel_id','{{%sip_channel}}','id','SET NULL','CASCADE');
        $this->dropForeignKey('sip_channel_attraction_channel_fk','{{%sip_channel}}');
        $this->dropColumn('{{%sip_channel}}','attraction_channel_id');
    }
}
