<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160407_093024_add_tag_id_to_call extends Migration
{
    public function up()
    {
        $this->addColumn('call', 'tag_id', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('call', 'tag_id');
    }
}
