<?php

use yii\db\Migration;

/**
 * Class m190129_135737_update_contact_first_phone_field_type
 */
class m190129_135737_update_contact_first_phone_field_type extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->alterColumn('contact', 'first_phone', 'varchar(255)');
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190129_135737_update_contact_first_phone_field_type cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190129_135737_update_contact_first_phone_field_type cannot be reverted.\n";

        return false;
    }
    */
}
