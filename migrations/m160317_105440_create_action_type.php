<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105440_create_action_type extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%action_type}}', [
            'id' => Schema::TYPE_PK,
            'name' => Schema::TYPE_STRING . " NOT NULL",
        ], $tableOptions);
        $this->insert('{{%action_type}}', array(
            'name' => 'scheduled_call',
        ));
        $this->insert('{{%action_type}}', array(
            'name' => 'scheduled_email',
        ));
    }

    public function down() {
        $this->dropTable('{{%action_type}}');
    }
}
