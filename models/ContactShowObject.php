<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_show_object".
 *
 * @property integer $id
 * @property integer $contact_show_id
 * @property integer $object_id
 */
class ContactShowObject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact_show_object';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_show_id', 'object_id'], 'required'],
            [['contact_show_id', 'object_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'contact_show_id' => 'Contact Show ID',
            'object_id' => 'Object ID',
        ];
    }
}
