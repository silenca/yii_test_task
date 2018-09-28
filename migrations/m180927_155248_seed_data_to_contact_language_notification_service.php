<?php

use yii\db\Migration;

class m180927_155248_seed_data_to_contact_language_notification_service extends Migration
{
    public function up()
    {
        $this->insert('{{%contact_notification_service}}', [
            'name' => 'SMS',
        ]);
        $this->insert('{{%contact_notification_service}}', [
            'name' => 'Viber',
        ]);
        $this->insert('{{%contact_notification_service}}', [
            'name' => 'Звонок',
        ]);
        $this->insert('{{%contact_notification_service}}', [
            'name' => 'Telegram',
        ]);
        $this->insert('{{%contact_language}}', [
            'name' => 'Русский', 'slug' => 'РУС']);
        $this->insert('{{%contact_language}}', [
            'name' => 'Украинский', 'slug' => 'УКР',
        ]);
    }

    public function down()
    {

    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
