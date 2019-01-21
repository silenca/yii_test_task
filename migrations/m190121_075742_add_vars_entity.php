<?php

use yii\db\Migration;

/**
 * Class m190121_075742_add_vars_entity
 */
class m190121_075742_add_vars_entity extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('vars', [
            'id' => $this->primaryKey(),
            'type' => $this->integer(11),
            'name' => $this->string(100)->notNull(),
            'value' => $this->string(1000),
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropTable('contacts_visits');
    }
}