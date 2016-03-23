<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160323_111637_add_viewed_to_action extends Migration
{
    public function up() {
        $this->addColumn('{{%action}}', 'viewed', 'TINYINT (1) NOT NULL DEFAULT 0');
    }

    public function down() {
        $this->dropColumn('{{%action}}', 'viewed');
    }
}
