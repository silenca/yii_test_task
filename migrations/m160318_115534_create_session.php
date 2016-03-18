<?php

use yii\db\Migration;
use yii\db\Schema;
use yii\db\Expression;
use yii\db\Query;

class m160318_115534_create_session extends Migration
{
    public function up()
    {
        $tableOptions = null;
        if ($this->db->driverName === 'mysql') {
            $tableOptions = 'CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE=InnoDB';
        }

        if (!$this->tableExists('session')) {
            $this->createTable('session', [
                'id' => 'char(40) NOT NULL',
                'expire' => 'int(11) DEFAULT NULL',
                'data' => 'longblob'
            ], $tableOptions);
        }


    }

    public function down()
    {
        $this->dropTable('session');
    }

    public function tableExists($tableName)
    {
        return in_array($tableName, Yii::$app->db->schema->tableNames);
    }
}
