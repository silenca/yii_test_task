<?php

use yii\db\Migration;

class m160322_120504_update_user_notify_key extends Migration
{
    public function up()
    {
        $this->update('{{%user}}', ['notification_key' => '70RfFScy-C4-aep1Qx2VEWw_6mTliK32'], 'id = 1');
        $this->update('{{%user}}', ['notification_key' => '70RfFScy-C4-aep1Qx2VEWw_6mTliK33'], 'id = 2');
        $this->update('{{%user}}', ['notification_key' => '70RfFScy-C4-aep1Qx2VEWw_6mTliK34'], 'id = 3');
        $this->update('{{%user}}', ['notification_key' => '70RfFScy-C4-aep1Qx2VEWw_6mTliK35'], 'id = 4');
        $this->update('{{%user}}', ['notification_key' => '70RfFScy-C4-aep1Qx2VEWw_6mTliK36'], 'id = 5');
    }

    public function down()
    {
        echo "m160322_120504_update_user_notify_key cannot be reverted.\n";

        return false;
    }
}
