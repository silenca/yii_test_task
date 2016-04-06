<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160405_143025_add_call_order_token_to_call extends Migration
{
    public function up()
    {
        $this->addColumn('{{%call}}', 'call_order_token', $this->string(50));
    }

    public function down()
    {
        $this->dropColumn('{{%call}}', 'call_order_token');
    }
}
