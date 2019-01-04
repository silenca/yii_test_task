<?php

use yii\db\Migration;

/**
 * Handles the creation of table `departments`.
 */
class m181228_161639_create_departments_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('departments', [
            'id' => $this->primaryKey(),
            'title' => $this->string(50)->notNull(),
            'api_url' => $this->string(250)->notNull(),
            'api_send_url' => $this->string(250)->notNull(),
        ]);

        $this->insert('departments', array(
            'title' => 'Нивки',
            'api_url' => 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1CDCBA80BCACD1E/I:PACK',
            'api_send_url' => 'http://91.225.122.210:5080/records',
        ));
        $this->insert('departments', array(
            'title' => 'Осокорки',
            'api_url' => 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1CDCBA80BCACD1E/I:PACK',
            'api_send_url' => 'http://91.225.122.210:5080/records',
        ));
        $this->insert('departments', array(
            'title' => 'Феофания',
            'api_url' => 'http://91.225.122.210:8080/api/H:1D13C88C20AA6C6/D:WORK/D:1D13C9303C946F9/C:1CDCBA80BCACD1E/I:PACK',
            'api_send_url' => 'http://91.225.122.210:5080/records',
        ));
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('departments');
    }
}
