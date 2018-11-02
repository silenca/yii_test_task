<?php

use yii\db\Migration;

class m181001_122747_add_city_column_to_the_db_and_system extends Migration
{
    public function up()
    {
        $this->addColumn('{{%contact}}', 'city', $this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%contact}}', 'city');

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
