<?php

use yii\db\Migration;

/**
 * Handles the creation for table `contact_notification_service`.
 */
class m180927_150027_create_contact_notification_service_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('contact_notification_service', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('contact_notification_service');
    }
}
