<?php

use yii\db\Migration;

/**
 * Class m190125_151514_update_sip_channel_model
 */
class m190125_151514_update_sip_channel_model extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('sip_channel', 'name', $this->string(250));
        $this->addColumn('sip_channel', 'is_active', $this->integer(4));

        $this->execute('UPDATE `sip_channel` SET `name` = `login`, `is_active` = 1');

        $this->dropColumn('sip_channel', 'login');
        $this->dropColumn('sip_channel', 'port');
        $this->dropColumn('sip_channel', 'password');
        $this->dropColumn('sip_channel', 'phone_number');
        $this->dropColumn('sip_channel', 'host');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->addColumn('sip_channel', 'login', $this->string(255)->notNull());
        $this->addColumn('sip_channel', 'port', $this->integer()->notNull());
        $this->addColumn('sip_channel', 'password', $this->string(255)->notNull());
        $this->addColumn('sip_channel', 'phone_number', $this->string(20)->notNull());
        $this->addColumn('sip_channel', 'host', $this->string(255)->notNull());

        $this->execute('UPDATE `sip_channel` SET `login` = `name`');

        $this->dropColumn('sip_channel', 'name');
        $this->dropColumn('sip_channel', 'is_active');
    }
}
