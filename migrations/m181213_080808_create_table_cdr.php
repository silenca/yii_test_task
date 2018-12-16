<?php

use yii\db\Migration;

/**
 * Class m181213_080808_create_table_cdr
 */
class m181213_080808_create_table_cdr extends Migration
{
    /**
     * {@inheritdoc}
     */
    public function safeUp()
    {
        $this->createTable('cdr',[
            'id' => $this->primaryKey(),
            'accountcode' => $this->string(80),
            'src' => $this->string(80),
            'dst' => $this->string(80),
            'dcontext' => $this->string(80),
            'clid' => $this->string(80),
            'channel' => $this->string(80),
            'dstchannel' => $this->string(80),
            'lastapp' => $this->string(80),
            'lastdata' => $this->string(80),
            'start' => $this->dateTime(),
            'answer' => $this->dateTime(),
            'end' => $this->dateTime(),
            'duration' => $this->integer(),
            'billsec' => $this->integer(),
            'disposition' => $this->string(45),
            'amaflags' => $this->string(45),
            'userfield' => $this->string(256),
            'uniqueid' => $this->string(150),
            'record' => $this->string(250),
            'linkedid' => $this->string(150),
            'peeraccount' => $this->string(20),
            'sequence' => $this->integer()
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function safeDown()
    {
        return $this->dropTable('cdr');
    }
}
