<?php

use yii\db\Migration;

class m181001_081057_remove_columns_from_contacts extends Migration
{
    public function up()
    {
        $this->dropColumn('{{%contact}}', 'region');
        $this->dropColumn('{{%contact}}', 'area');
        $this->dropColumn('{{%contact}}', 'city');
        $this->dropColumn('{{%contact}}', 'street');
        $this->dropColumn('{{%contact}}', 'house');
        $this->dropColumn('{{%contact}}', 'flat');
    }

    public function down()
    {
        echo "m181001_081057_remove_columns_from_contacts cannot be reverted.\n";

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
