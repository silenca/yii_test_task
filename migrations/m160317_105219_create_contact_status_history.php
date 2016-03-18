<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105219_create_contact_status_history extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%contact_status_history}}', [
            'id' => Schema::TYPE_PK,
            'contact_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'manager_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'date_time' => Schema::TYPE_DATETIME . ' NOT NULL',
            'status' => "ENUM('lead','deal') NOT NULL DEFAULT 'lead'",
        ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%contact_status_history}}');
    }
}
