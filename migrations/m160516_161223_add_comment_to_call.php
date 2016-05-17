<?php

use yii\db\Migration;

class m160516_161223_add_comment_to_call extends Migration
{
    public function up()
    {
        $this->addColumn('call', 'comment', $this->text());
    }

    public function down()
    {
        $this->dropColumn('call', 'comment');
    }
}
