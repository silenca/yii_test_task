<?php

use yii\db\Migration;

class m160622_111058_add_is_delete_to_user_table extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'is_deleted', 'TINYINT (1) NOT NULL DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('user', 'is_deleted');
    }
}
