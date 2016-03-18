<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160317_105037_create_contact_history extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=MyISAM';
        }

        $this->createTable('{{%contact_history}}', [
            'id' => Schema::TYPE_PK,
            'contact_id' => Schema::TYPE_INTEGER .' NOT NULL',
            'text' => Schema::TYPE_TEXT,
            'datetime' => Schema::TYPE_DATETIME,
            'type' => "ENUM('comment','new_contact','scheduled_call','scheduled_email')"

        ], $tableOptions);
        $this->createIndex('index_contact_id', '{{%contact_history}}', 'contact_id');
    }

    public function down()
    {
        $this->dropTable('{{%contact_history}}');
    }
}
