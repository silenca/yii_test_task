<?php

use yii\db\Migration;

/**
 * Class m181120_122914_edit_users_table__add_access_key__seed
 */
class m181120_122914_edit_users_table__add_access_key__seed extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%user}}', 'access_token', $this->string());
        $this->insert('{{%user}}', [
            'int_id' => 606,
            'email' => 'medium.integration@gmail.com',
            'firstname' => 'Medium',
            'lastname' => 'Integration',
            'patronymic' => 'Integration',
            'created_at' => 0,
            'updated_at' => 0,
            'is_deleted' => 0,
            'auth_key' => ' ',
            'role' => 5,
            'access_token' => 'ffO3MoJo3e6aqPWYq3ltWa17ERpWgl5C',
            'password_hash' => '$2y$13$/S1wfq/FAmSNxbrr1EqHY.jFpGeQ5yGtK8a1TBUO8/31kYSwGWf5a'
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%contact}}', 'medium_oid');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181120_122914_edit_users_table__add_access_key__seed cannot be reverted.\n";

        return false;
    }
    */
}
