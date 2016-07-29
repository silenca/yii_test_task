<?php

use yii\db\Migration;

class m160728_094838_add_sended_crm_field_to_contact_and_call extends Migration
{
    public function up()
    {
        $this->addColumn('contact', 'sended_crm', 'TINYINT(1) DEFAULT 0');
        $this->addColumn('call', 'sended_crm', 'TINYINT(1) DEFAULT 0');
    }

    public function down()
    {
        $this->dropColumn('contact', 'sended_crm');
        $this->dropColumn('call', 'sended_crm');
    }
}
