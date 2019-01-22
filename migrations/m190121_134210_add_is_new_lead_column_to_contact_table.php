<?php

use yii\db\Migration;

/**
 * Handles adding is_new_lead to table `contact`.
 */
class m190121_134210_add_is_new_lead_column_to_contact_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('contact', 'is_new_lead', $this->integer());
        $this->addColumn('contacts_visits', 'manager_id', $this->integer());

        $this->execute('ALTER TABLE `contact` CHANGE COLUMN `lastSyncDate` `lastSyncDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `link_with`;');
        $this->execute('ALTER TABLE `contact` ADD COLUMN `create_date` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP AFTER `lastSyncDate`;');
        $this->execute('UPDATE contact SET create_date=lastSyncDate;');
        $this->execute('ALTER TABLE `contact` CHANGE COLUMN `lastSyncDate` `lastSyncDate` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `link_with`;');

        $this->execute('UPDATE contacts_visits SET manager_id = 1;');
        $this->execute('ALTER TABLE `contacts_visits`
	CHANGE COLUMN `create_date` `create_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP AFTER `id`,
	CHANGE COLUMN `edit_date` `edit_date` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP AFTER `create_date`;');

    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contact', 'is_new_lead');
        $this->dropColumn('contact', 'create_date');
        $this->dropColumn('contacts_visits', 'manager_id');
    }
}
