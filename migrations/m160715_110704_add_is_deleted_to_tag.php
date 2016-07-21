<?php

use yii\db\Migration;

class m160715_110704_add_is_deleted_to_tag extends Migration
{
    public function up()
    {
        $this->addColumn('tag', 'is_deleted', 'TINYINT (1) NOT NULL DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('tag', 'is_deleted');
    }
}
