<?php

use yii\db\Migration;
use app\models\Departments;

/**
 * Class m190116_142540_update_departments_table
 */
class m190116_142540_update_departments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        Departments::updateAll(['api_url'=>'H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9']);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        echo "m190116_142540_update_departments_table cannot be reverted.\n";

        return false;
    }

    /*
    // Use up()/down() to run migration code without a transaction.
    public function up()
    {

    }

    public function down()
    {
        echo "m190116_142540_update_departments_table cannot be reverted.\n";

        return false;
    }
    */
}
