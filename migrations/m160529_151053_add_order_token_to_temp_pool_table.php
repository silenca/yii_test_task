<?php

use yii\db\Migration;

class m160529_151053_add_order_token_to_temp_pool_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%temp_contacts_pool}}', 'order_token', $this->string());
    }

    public function down()
    {
        $this->dropColumn('call', '{{%temp_contacts_pool}}');
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
