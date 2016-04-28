<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160428_112046_add_tag_id_to_contact_called extends Migration
{
    public function up()
    {
        $this->addColumn('contact_called', 'tag_id', $this->integer());
    }

    public function down()
    {
        $this->dropColumn('contact_called', 'tag_id');
    }
}
