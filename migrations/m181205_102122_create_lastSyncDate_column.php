<?php

use yii\db\Migration;

/**
 * Class m181205_102122_create_lastSyncDate_column
 */
class m181205_102122_create_lastSyncDate_column extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('contact', 'lastSyncDate','DATETIME');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contact','lastSyncDate');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m181205_102122_create_lastSyncDate_column cannot be reverted.\n";

        return false;
    }
    */
}
