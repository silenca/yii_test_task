<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160316_071905_add_users extends Migration
{
    public function up() {
        $now = new Expression('NOW()');

        $this->insert('{{%user}}', array(
            'firstname' => 'admin',
            'lastname' => 'admin',
            'patronymic' => 'admin',
            'password_hash' => '$2y$13$/S1wfq/FAmSNxbrr1EqHY.jFpGeQ5yGtK8a1TBUO8/31kYSwGWf5a', //admin
            'email' => 'admin@call.ru',
            'int_id' => '800',
            'auth_key' => '',
            'role' => 15,
            'created_at' => strtotime($now),
            'updated_at' => strtotime($now),
        ));
        $this->insert('{{%user}}', array(
            'firstname' => 'manager',
            'lastname' => 'manager',
            'patronymic' => 'manager',
            'password_hash' => '$2y$13$/S1wfq/FAmSNxbrr1EqHY.jFpGeQ5yGtK8a1TBUO8/31kYSwGWf5a', //admin
            'email' => 'manager@call.ru',
            'int_id' => '900',
            'auth_key' => '',
            'role' => 1,
            'created_at' => strtotime($now),
            'updated_at' => strtotime($now),
        ));
        $this->insert('{{%user}}', array(
            'firstname' => 'operator',
            'lastname' => 'operator',
            'patronymic' => 'operator',
            'password_hash' => '$2y$13$/S1wfq/FAmSNxbrr1EqHY.jFpGeQ5yGtK8a1TBUO8/31kYSwGWf5a', //admin
            'email' => 'operator@call.ru',
            'int_id' => '1000',
            'auth_key' => '',
            'role' => 5,
            'created_at' => strtotime($now),
            'updated_at' => strtotime($now),
        ));
    }

    public function down()
    {
        echo "m160316_071905_add_users cannot be reverted.\n";

        return false;
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
