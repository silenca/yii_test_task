<?php

use yii\db\Migration;
use yii\db\Expression;

class m160322_110826_update_users extends Migration
{
    public function up()
    {
        $this->update('{{%user}}', ['int_id' => 601,'email' => 'admin@gmail.com'], 'id = 1');
        $this->update('{{%user}}', ['int_id' => 602, 'email' => 'manager@gmail.com', 'role' => 5], 'id = 2');
        $this->update('{{%user}}', ['int_id' => 603, 'email' => 'operator_1@gmail.com', 'role' => 1], 'id = 3');

        $now = new Expression('NOW()');

        $this->insert('{{%user}}', array(
            'firstname' => 'operator_2',
            'lastname' => 'operator_2',
            'patronymic' => 'operator_2',
            'password_hash' => '$2y$13$/S1wfq/FAmSNxbrr1EqHY.jFpGeQ5yGtK8a1TBUO8/31kYSwGWf5a', //admin
            'email' => 'operator_2@gmail.com',
            'int_id' => 604,
            'auth_key' => '',
            'role' => 1,
            'created_at' => strtotime($now),
            'updated_at' => strtotime($now),
        ));
        $this->insert('{{%user}}', array(
            'firstname' => 'operator_3',
            'lastname' => 'operator_3',
            'patronymic' => 'operator_3',
            'password_hash' => '$2y$13$/S1wfq/FAmSNxbrr1EqHY.jFpGeQ5yGtK8a1TBUO8/31kYSwGWf5a', //admin
            'email' => 'operator_3@gmail.com',
            'int_id' => 605,
            'auth_key' => '',
            'role' => 1,
            'created_at' => strtotime($now),
            'updated_at' => strtotime($now),
        ));
    }

    public function down()
    {
        echo "m160322_110826_update_users cannot be reverted.\n";

        return false;
    }
}
