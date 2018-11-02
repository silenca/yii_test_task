<?php

use yii\db\Migration;

class m180928_120104_add_is_broadcast_column_to_contact extends Migration
{
    public function up()
    {
        $this->addColumn('{{%contact}}', 'is_broadcast', $this->boolean()->defaultValue(0));
    }

    public function down()
    {
        $this->dropColumn('{{%contact}}', 'is_broadcast');
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
