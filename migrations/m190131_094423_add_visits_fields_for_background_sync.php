<?php

use yii\db\Migration;

/**
 * Class m190131_094423_add_visits_fields_for_background_sync
 */
class m190131_094423_add_visits_fields_for_background_sync extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('contacts_visits', 'cabinet_oid', $this->string(250)->null());
        $this->addColumn('contacts_visits', 'doctor_oid', $this->string(250)->null());
        $this->addColumn('contacts_visits', 'cabinet_name', $this->string(250)->null());
        $this->addColumn('contacts_visits', 'doctor_name', $this->string(250)->null());
        $this->addColumn('contacts_visits', 'comment', $this->text()->defaultValue(''));
        $this->addColumn('contacts_visits', 'time', $this->integer()->notNull());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('contacts_visits', 'cabinet_oid');
        $this->dropColumn('contacts_visits', 'doctor_oid');
        $this->dropColumn('contacts_visits', 'cabinet_name');
        $this->dropColumn('contacts_visits', 'doctor_name');
        $this->dropColumn('contacts_visits', 'comment');
        $this->dropColumn('contacts_visits', 'time');
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190131_094423_add_visits_fields_for_background_sync cannot be reverted.\n";

        return false;
    }
    */
}
