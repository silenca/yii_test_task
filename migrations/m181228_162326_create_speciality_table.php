<?php

use yii\db\Migration;

/**
 * Handles the creation of table `speciality`.
 */
class m181228_162326_create_speciality_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('speciality', [
            'id' => $this->primaryKey(),
            'title' => $this->string(50)->notNull(),
            'oid' => $this->string()->notNull(),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('speciality');
    }
}
