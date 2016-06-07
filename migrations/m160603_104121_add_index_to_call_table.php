<?php

use yii\db\Migration;

class m160603_104121_add_index_to_call_table extends Migration
{
    public function up()
    {
        $this->createIndex('call_tag_id', '{{%call}}', 'tag_id', false);
        $this->createIndex('call_contact_id', '{{%call}}', 'contact_id', false);
    }

    public function down()
    {
        $this->dropIndex('call_tag_id', '{{%call}}');
        $this->dropIndex('call_contact_id', '{{%call}}');
    }
}
