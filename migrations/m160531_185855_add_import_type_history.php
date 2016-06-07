<?php

use yii\db\Migration;

class m160531_185855_add_import_type_history extends Migration
{
    public function up()
    {
        $this->alterColumn('{{%contact_history}}', 'type', "ENUM('comment','new_contact','scheduled_call','scheduled_email','ring_round','imported_comment')");

    }

    public function down()
    {
        $this->alterColumn('{{%contact_history}}', 'type', "ENUM('comment','new_contact','scheduled_call','scheduled_email','ring_round')");
    }
}
