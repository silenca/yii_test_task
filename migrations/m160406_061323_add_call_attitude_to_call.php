<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160406_061323_add_call_attitude_to_call extends Migration
{
    public function up()
    {
        $this->addColumn('{{%call}}', 'attitude_level', $this->smallInteger(1));
    }

    public function down()
    {
        $this->dropColumn('{{%call}}', 'attitude_level');
    }
}
