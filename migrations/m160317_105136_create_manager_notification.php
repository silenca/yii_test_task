<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105136_create_manager_notification extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%manager_notification}}', [
            'id' => Schema::TYPE_PK,
            'system_date' => Schema::TYPE_DATETIME,
            'manager_id' => Schema::TYPE_INTEGER . " NOT NULL",
            'type' => "ENUM('call_missed','scheduled_call','scheduled_email')",
            'contact_id' => Schema::TYPE_INTEGER,
            'phone_number' => Schema::TYPE_BIGINT,
            'viewed' => "TINYINT (1) NOT NULL DEFAULT 0",
        ], $tableOptions);

        $this->addForeignKey('manager_notification_contact', '{{%manager_notification}}', 'contact_id', '{{%contact}}', 'id','CASCADE','CASCADE');
        $this->addForeignKey('manager_notification_manager', '{{%manager_notification}}', 'manager_id', '{{%user}}', 'id','CASCADE','CASCADE');

        $this->createIndex('index_contact_id', '{{%manager_notification}}', 'contact_id');
        $this->createIndex('index_manager_id', '{{%manager_notification}}', 'manager_id');
    }

    public function down() {
        $this->dropTable('{{%manager_notification}}');
    }
}
