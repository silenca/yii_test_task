<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160316_072500_create_contact_comment extends Migration
{
    public function up() {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }

        $this->createTable('{{%contact_comment}}', [
            'id' => Schema::TYPE_PK,
            'contact_id' => Schema::TYPE_INTEGER .' NOT NULL',
            'comment' => Schema::TYPE_TEXT,
            'datetime' => Schema::TYPE_DATETIME . ' NOT NULL',
        ], $tableOptions);
        $this->createIndex('index_contact_id', '{{%contact_comment}}', 'contact_id');
    }

    public function down() {
        $this->dropTable('{{%contact_comment}}');
    }
}
