<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160420_081022_create_contact_ring_round extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('contact_ring_round', [
            'id' => $this->primaryKey(),
            'contact_id' => Schema::TYPE_INTEGER . " NOT NULL",
            'system_date' => Schema::TYPE_DATETIME . " NOT NULL",
            'manager_id' => Schema::TYPE_INTEGER . " NOT NULL",
        ], $tableOptions);

        $this->addForeignKey('contact_ring_round_contact', '{{%contact_ring_round}}', 'contact_id', '{{%contact}}', 'id','CASCADE','CASCADE');
        $this->addForeignKey('contact_ring_round_manager', '{{%contact_ring_round}}', 'manager_id', '{{%user}}', 'id','CASCADE','CASCADE');

        $this->createIndex('index_contact_id', '{{%contact_ring_round}}', 'contact_id');
        $this->createIndex('index_manager_id', '{{%contact_ring_round}}', 'manager_id');
    }

    public function down()
    {
        $this->dropTable('contact_ring_round');
    }
}
