<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105721_create_manager_notification_action extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%manager_notification_action}}', [
            'id' => Schema::TYPE_PK,
            'manager_notification_id' => Schema::TYPE_INTEGER . " NOT NULL",
            'action_id' => Schema::TYPE_INTEGER . " NOT NULL",
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%manager_notification_action}}');
    }
}
