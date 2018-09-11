<?php

use yii\db\Migration;

/**
 * Handles the creation for table `sip_channel`.
 */
class m180911_072225_create_sip_channel_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function safeUp()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }
        $this->createTable('{{%sip_channel}}', [
            'id' => $this->primaryKey(),
            'phone_number' => $this->string(20)->notNull(),
            'host' => $this->string(255)->notNull(),
            'port' => $this->integer()->notNull(),
            'login' => $this->string(255)->notNull(),
            'password' => $this->string(255)->notNull(),
        ],$tableOptions);
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropTable('{{%sip_channel}}');
    }
}
