<?php

use yii\db\Migration;

class m160316_072111_create_contact extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            // http://stackoverflow.com/questions/766809/whats-the-difference-between-utf8-general-ci-and-utf8-unicode-ci
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        $this->createTable('{{%contact}}', [
            'id' => $this->primaryKey(),
            'int_id' => $this->integer()->notNull(),
            'name' => $this->string(150),
            'surname' => $this->string(150),
            'middle_name' => $this->string(150),
            'first_phone' => $this->string(20),
            'second_phone' => $this->string(20),
            'third_phone' => $this->string(20),
            'fourth_phone' => $this->string(20),
            'first_email' => $this->string(),
            'second_email' => $this->string(),
            'country' => $this->string(150),
            'region' => $this->string(150),
            'area' => $this->string(150),
            'city' => $this->string(150),
            'street' => $this->string(150),
            'house' => $this->string(150),
            'flat' => $this->string(150),
            'status' => "ENUM('lead', 'deal') NOT NULL DEFAULT 'lead'",
            'manager_id' => $this->integer(),
            'is_deleted' => 'TINYINT (1) NOT NULL DEFAULT 0',
        ], $tableOptions);
    }

    public function down()
    {
        $this->dropTable('{{%contact}}');
    }
}
