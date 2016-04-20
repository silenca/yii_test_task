<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160415_111858_create_temp_contacts_pool extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('temp_contacts_pool', [
            'id' => $this->primaryKey(),
            'contact_id' => $this->integer(),
            'manager_id' => $this->integer(),
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('temp_contacts_pool');
    }
}
