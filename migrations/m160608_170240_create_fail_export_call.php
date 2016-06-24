<?php

use yii\db\Migration;
use yii\db\Schema;

class m160608_170240_create_fail_export_call extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%fail_export_call}}', [
            'id' => Schema::TYPE_PK,
            'call_id' => Schema::TYPE_INTEGER . ' NOT NULL',
            'date_time' => Schema::TYPE_DATETIME . ' NOT NULL',
        ], $tableOptions);
    }

    public function down() {
        $this->dropTable('{{%fail_export_contacts}}');
    }
}
