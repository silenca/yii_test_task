<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160407_092255_alter_tag extends Migration
{
    public function up()
    {
        $this->addColumn('tag', 'as_task', 'TINYINT (1) NOT NULL DEFAULT 0');
        $this->addColumn('tag', 'start_date', $this->dateTime());
        $this->addColumn('tag', 'end_date', $this->dateTime());
        $this->addColumn('tag', 'script', $this->text());
    }

    public function down()
    {
        $this->dropColumn('tag', 'as_task');
        $this->dropColumn('tag', 'start_date');
        $this->dropColumn('tag', 'end_date');
        $this->dropColumn('tag', 'script');
    }

    /*
    // Use safeUp/safeDown to run migration code within a transaction
    public function safeUp()
    {
    }

    public function safeDown()
    {
    }
    */
}
