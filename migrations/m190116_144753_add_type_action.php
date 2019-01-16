<?php

use yii\db\Migration;

/**
 * Class m190116_144753_add_type_action
 */
class m190116_144753_add_type_action extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $actionType = new \app\models\ActionType();
        $actionType->name = 'scheduled_visit';
        $actionType->save();


        $this->execute("ALTER TABLE `contact_history`
	CHANGE COLUMN `type` `type` ENUM('comment','new_contact','scheduled_call','scheduled_email','ring_round','imported_comment','scheduled_visit') NULL DEFAULT NULL COLLATE 'utf8_unicode_ci' AFTER `datetime`;");
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190116_144753_add_type_action cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190116_144753_add_type_action cannot be reverted.\n";

        return false;
    }
    */
}
