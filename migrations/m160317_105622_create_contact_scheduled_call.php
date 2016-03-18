<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105622_create_contact_scheduled_call extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%contact_scheduled_call}}', [
            'id' => Schema::TYPE_PK,
            'contact_id' => Schema::TYPE_INTEGER . " NOT NULL",
            'system_date' => Schema::TYPE_DATETIME . " NOT NULL",
            'schedule_date' => Schema::TYPE_DATETIME,
            'manager_id' => Schema::TYPE_INTEGER . " NOT NULL",
        ], $tableOptions);

        $this->addForeignKey('contact_scheduled_call_contact', '{{%contact_scheduled_call}}', 'contact_id', '{{%contact}}', 'id','CASCADE','CASCADE');
        $this->addForeignKey('contact_scheduled_call_manager', '{{%contact_scheduled_call}}', 'manager_id', '{{%user}}', 'id','CASCADE','CASCADE');

        $this->createIndex('index_contact_id', '{{%contact_scheduled_call}}', 'contact_id');
        $this->createIndex('index_manager_id', '{{%contact_scheduled_call}}', 'manager_id');
    }

    public function down() {
        $this->dropTable('{{%contact_scheduled_call}}');
    }
}
