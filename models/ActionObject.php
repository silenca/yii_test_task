<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "action_object".
 *
 * @property integer $id
 * @property integer $action_id
 * @property integer $object_id
 */
class ActionObject extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'action_object';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['action_id', 'object_id'], 'required'],
            [['action_id', 'object_id'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'action_id' => 'Action ID',
            'object_id' => 'Object ID',
        ];
    }
}
