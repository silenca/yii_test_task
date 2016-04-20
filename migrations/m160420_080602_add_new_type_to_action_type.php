<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160420_080602_add_new_type_to_action_type extends Migration
{
    public function up()
    {
        $this->insert('{{%action_type}}', array(
            'name' => 'ring_round',
        ));
    }

    public function down()
    {
        $this->delete('{{%action_type}}', array(
            'name' => 'ring_round',
        ));
    }
}
