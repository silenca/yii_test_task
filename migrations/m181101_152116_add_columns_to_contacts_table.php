<?php

use yii\db\Migration;

class m181101_152116_add_columns_to_contacts_table extends Migration
{
    public function up()
    {
        $this->addColumn('{{%contact}}', 'medium_oid', $this->string());
    }

    public function down()
    {
        $this->dropColumn('{{%contact}}', 'medium_oid');
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
