<?php

use yii\db\Migration;

class m180927_154247_add_foreigns_to_contact_table extends Migration
{
    public function safeUp()
    {
        $this->addColumn('{{%contact}}', 'language_id', $this->integer());
        $this->addColumn('{{%contact}}', 'notification_service_id', $this->integer());

        // creates index for column `language_id`
        $this->createIndex(
            'idx-contact-language_id',
            '{{%contact}}',
            'language_id'
        );

        // add foreign key for table `contact_language`
        $this->addForeignKey(
            'fk-contact-language_id',
            '{{%contact}}',
            'language_id',
            '{{%contact_language}}',
            'id'
        );

        // creates index for column `notification_channel_id`
        $this->createIndex(
            'idx-contact-notification_service_id',
            '{{%contact}}',
            'notification_service_id'
        );

        // add foreign key for table `contact_notification_channel`
        $this->addForeignKey(
            'fk-contact-notification_service_id',
            '{{%contact}}',
            'notification_service_id',
            '{{%contact_notification_service}}',
            'id'
        );
    }

    public function safeDown()
    {
        $this->dropColumn('{{%contact}}', 'language_id');
        $this->dropColumn('{{%contact}}', 'notification_service_id');

        // drops foreign key for table `contact_language`
        $this->dropForeignKey(
            'fk-contact-language_id',
            '{{%contact}}'
        );

        // drops index for column `language_id`
        $this->dropIndex(
            'idx-contact-language_id',
            '{{%contact}}'
        );

        // drops foreign key for table `contact_notification_channel`
        $this->dropForeignKey(
            'fk-contact-notification_service_id',
            '{{%contact}}'
        );

        // drops index for column `notification_channel_id`
        $this->dropIndex(
            'idx-contact-notification_service_id',
            '{{%contact}}'
        );
    }


}
