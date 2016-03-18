<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105506_create_call_manager extends Migration
{
    public function up() {
        $tableOptions = null;
        $this->createTable('{{%call_manager}}', [
            'id' => Schema::TYPE_PK,
            'call_id' => Schema::TYPE_INTEGER . " NOT NULL",
            'manager_id' => Schema::TYPE_INTEGER . " NOT NULL",
        ], $tableOptions);

        $this->addForeignKey('call_manager_call', '{{%call_manager}}', 'call_id', '{{%call}}', 'id', 'CASCADE', 'CASCADE');
        $this->addForeignKey('call_manager_manager', '{{%call_manager}}', 'manager_id', '{{%user}}', 'id', 'CASCADE', 'CASCADE');
    }

    public function down() {
        $this->dropForeignKey('call_manager_call', '{{%call}}');
        $this->dropForeignKey('call_manager_manager', '{{%call}}');
        $this->dropTable('{{%call_manager}}');
    }
}
