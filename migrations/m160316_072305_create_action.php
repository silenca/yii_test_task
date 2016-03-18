<?php

use yii\db\Migration;
use yii\db\Schema;

class m160316_072305_create_action extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%action}}', [
            'id' => Schema::TYPE_PK,
            'system_date' => Schema::TYPE_DATETIME . ' NOT NULL',
            'contact_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'manager_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'schedule_date' => Schema::TYPE_DATETIME,
            'action_type_id' => Schema::TYPE_INTEGER ." NOT NULL",
        ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%action}}');
    }
}
