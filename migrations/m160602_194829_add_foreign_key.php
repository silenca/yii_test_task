<?php

use yii\db\Migration;

class m160602_194829_add_foreign_key extends Migration
{
    public function up()
    {
        $this->addForeignKey('contact_tag_contact_id','{{%contact_tag}}','contact_id','{{%contact}}', 'id', 'CASCADE','CASCADE');
        $this->addForeignKey('contact_tag_tag_id','{{%contact_tag}}','tag_id','{{%tag}}', 'id', 'CASCADE','CASCADE');
    }

    public function down()
    {
        echo "m160602_194829_add_foreign_key cannot be reverted.\n";

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
