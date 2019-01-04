<?php

use yii\db\Migration;

/**
 * Handles the creation of table `contact_visit`.
 */
class m190104_100427_create_contact_visit_log_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('contact_visit_log', [
            'id' => $this->primaryKey(),
            'date' => $this->dateTime(),
            'date_visit' => $this->dateTime(),
            'contact_id' => $this->integer('11'),
            'medium_oid' => $this->string()->notNull(),
            'request' => $this->text(),
            'response' => $this->text()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contact_visit');
    }
}
