<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105550_create_missed_call extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%missed_call}}', [
            'id' => Schema::TYPE_PK,
            'call_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'manager_id' => Schema::TYPE_INTEGER . ' NOT NULL',
        ], $tableOptions);

        $this->createIndex('index_call_id', '{{%missed_call}}', 'call_id');
        $this->createIndex('index_manager_id', '{{%missed_call}}', 'manager_id');

        $this->addForeignKey('missed_call_call', '{{%missed_call}}', 'call_id', '{{%call}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('missed_call_manager', '{{%missed_call}}', 'manager_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down() {
        $this->dropTable('{{%missed_call}}');
    }
}
