<?php

use yii\db\Migration;

/**
 * Handles adding password_sip to table `user`.
 */
class m190114_092654_add_password_sip_column_to_user_table extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->addColumn('user', 'password_sip', $this->string());
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        $this->dropColumn('user', 'password_sip');
    }
}
