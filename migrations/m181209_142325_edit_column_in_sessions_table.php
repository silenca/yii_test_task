<?php

use yii\db\Migration;

/**
 * Class m181209_142325_edit_column_in_sessions_table
 */
class m181209_142325_edit_column_in_sessions_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->dropColumn('{{%session}}', 'last_write');
        $this->addColumn('{{%session}}', 'last_write',$this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('{{%session}}', 'last_write');
        $this->addColumn('{{%session}}', 'last_write',$this->timestamp());
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181209_142325_edit_column_in_sessions_table cannot be reverted.\n";

        return false;
    }
    */
}
