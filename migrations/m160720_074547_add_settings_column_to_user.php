<?php

use yii\db\Migration;

class m160720_074547_add_settings_column_to_user extends Migration
{
    public function up()
    {
        $this->addColumn('user', 'settings', $this->text());
    }

    public function down()
    {
        $this->dropColumn('user', 'settings');
    }
}
