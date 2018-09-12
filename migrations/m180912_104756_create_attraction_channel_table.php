<?php

use yii\db\Migration;

/**
 * Handles the creation for table `attraction_channel`.
 */
class m180912_104756_create_attraction_channel_table extends Migration
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
        $this->createTable('{{%attraction_channel}}', [
            'id' => $this->primaryKey(),
            'name' => $this->string(255),
            'is_active' => $this->smallInteger(1)->notNull()->defaultValue(0),
            'type' => $this->smallInteger()->notNull()->defaultValue(0),
            'sip_channel_id'=> $this->integer(),
            'integration_type'=>$this->string(255),
        ],$tableOptions);
        $this->addForeignKey('attraction_channel_sip_channel_fk','{{%attraction_channel}}','sip_channel_id','{{%sip_channel}}','id','SET NULL','CASCADE');
    }

    /**
     * @inheritdoc
     */
    public function safeDown()
    {
        $this->dropForeignKey('attraction_channel_sip_channel_fk','{{%attraction_channel}}');
        $this->dropTable('{{%attraction_channel}}');
    }
}
