<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105648_create_contact_scheduled_email extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%contact_scheduled_email}}', [
            'id' => Schema::TYPE_PK,
            'contact_id' => Schema::TYPE_INTEGER . " NOT NULL",
            'system_date' => Schema::TYPE_DATETIME . " NOT NULL",
            'schedule_date' => Schema::TYPE_DATETIME,
            'manager_id' => Schema::TYPE_INTEGER . " NOT NULL",
        ], $tableOptions);

        $this->addForeignKey('contact_scheduled_email_contact', '{{%contact_scheduled_email}}', 'contact_id', '{{%contact}}', 'id','CASCADE','CASCADE');
        $this->addForeignKey('contact_scheduled_email_manager', '{{%contact_scheduled_email}}', 'manager_id', '{{%user}}', 'id','CASCADE','CASCADE');

        $this->createIndex('index_contact_id', '{{%contact_scheduled_email}}', 'contact_id');
        $this->createIndex('index_manager_id', '{{%contact_scheduled_email}}', 'manager_id');
    }

    public function down() {
        $this->dropTable('{{%contact_scheduled_email}}');
    }
}
