<?php

use yii\db\Migration;

/**
 * Class m181209_141600_add_columns_to_sessions_table
 */
class m181209_141600_add_columns_to_sessions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('{{%session}}', 'user_id', $this->bigInteger());
        $this->addColumn('{{%session}}', 'last_write',$this->timestamp());

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%session}}', 'user_id');
        $this->dropColumn('{{%session}}', 'last_write');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181209_141600_add_columns_to_sessions_table cannot be reverted.\n";

        return false;
    }
    */
}
