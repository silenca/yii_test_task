<?php

use yii\db\Migration;

/**
 * Handles the creation for table `contact_language`.
 */
class m180927_150019_create_contact_language_table extends Migration
{
    /**
     * @inheritdoc
     */
    public function up()
    {
        $this->createTable('contact_language', [
            'id' => $this->primaryKey(),
            'name' => $this->string(),
            'slug' => $this->string(),
        ]);
    }

    /**
     * @inheritdoc
     */
    public function down()
    {
        $this->dropTable('contact_language');
    }
}
