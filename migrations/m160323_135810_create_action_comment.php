<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160323_135810_create_action_comment extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }

        $this->createTable('{{%action_comment}}', [
            'id' => Schema::TYPE_PK,
            'action_id' => Schema::TYPE_INTEGER .' NOT NULL',
            'comment' => Schema::TYPE_TEXT,
            'datetime' => Schema::TYPE_DATETIME . ' NOT NULL',
        ], $tableOptions);
        $this->createIndex('index_action_id', '{{%action_comment}}', 'action_id');
    }

    public function down() {
        $this->dropTable('{{%action_comment}}');
    }
}
