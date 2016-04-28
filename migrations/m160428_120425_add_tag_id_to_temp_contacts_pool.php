<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160428_120425_add_tag_id_to_temp_contacts_pool extends Migration
{
    public function up()
    {
        $this->addColumn('temp_contacts_pool', 'tag_id', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('temp_contacts_pool', 'tag_id');
    }
}
