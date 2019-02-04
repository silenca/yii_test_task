<?php

use yii\db\Migration;

/**
 * Class m190131_093936_add_visits_sync_status_field
 */
class m190131_093936_add_visits_sync_status_field extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('contacts_visits', 'sync_status', $this->integer(4));

        $this->update('contacts_visits', ['sync_status' => 3]);

        $this->alterColumn('contacts_visits', 'sync_status', $this->integer(4)->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contacts_visits', 'sync_status');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190131_093936_add_visits_sync_status_field cannot be reverted.\n";

        return false;
    }
    */
}
