<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160420_082718_add_new_type_to_contact_history extends Migration
{
    public function up()
    {
        $this->alterColumn('contact_history', 'type', "ENUM('comment','new_contact','scheduled_call','scheduled_email', 'ring_round')");
    }

    public function down()
    {
        $this->alterColumn('contact_history', 'type', "ENUM('comment','new_contact','scheduled_call','scheduled_email')");
    }
}
