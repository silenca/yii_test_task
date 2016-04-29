<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160429_110606_drop_contact_called extends Migration
{
    public function up()
    {
        $this->dropTable('contact_called');
    }

    public function down()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('contact_called', [
            'id' => $this->primaryKey(),
            'contact_id' => $this->integer(),
            'call_id' => $this->integer(),
            'manager_id' => $this->integer(),
            'tag_id' => $this->integer(),
        ], $tableOptions);
    }
}
