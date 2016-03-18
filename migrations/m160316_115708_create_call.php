<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160316_115708_create_call extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%call}}', [
            'id' => Schema::TYPE_PK,
            'unique_id' => Schema::TYPE_STRING . '(50)  NOT NULL',
            'date_time' => Schema::TYPE_DATETIME . ' NOT NULL',
            'total_time' => Schema::TYPE_INTEGER,
            'answered_time' => Schema::TYPE_INTEGER,
            'type' => "ENUM('incoming','outgoing') NOT NULL DEFAULT 'incoming'",
            'contact_id' => Schema::TYPE_INTEGER,
            'phone_number' => Schema::TYPE_STRING . '(20)',
            'record' => Schema::TYPE_STRING,
            'status' => "ENUM('missed', 'new','failure','answered') NOT NULL DEFAULT 'new'",
        ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%call}}');
    }
}
