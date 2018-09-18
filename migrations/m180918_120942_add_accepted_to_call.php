<?php

use yii\db\Migration;

class m180918_120942_add_accepted_to_call extends Migration
{

    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
        $this->addColumn('{{%call}}','accepted',$this->smallInteger(1)->notNull()->defaultValue(0));
    }

    public function safeDown()
    {
        $this->dropColumn('{{%call}}','accepted');
    }
}
