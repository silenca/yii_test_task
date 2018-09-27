<?php

use yii\db\Migration;

class m180927_140020_add_columns_to_contact_table extends Migration
{
    public function up()
    {
        $this->addColumn('%contact', 'language', $this->string())->defaultValue('РУС');
        $this->addColumn('%contact', 'is_broadcast', $this->boolean())->defaultValue(0);
        $this->addColumn('%contact', 'notification_chanel', $this->string())->default('SMS');
    }

    public function down()
    {
        $this->addColumn('%contact', 'language', $this->string())->defaultValue('РУС');
        $this->addColumn('%contact', 'is_broadcast', $this->boolean())->defaultValue(0);
        $this->addColumn('%contact', 'notification_chanel', $this->string())->default('SMS');

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
