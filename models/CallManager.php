<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "call_manager".
 *
 * @property integer $id
 * @property integer $call_id
 * @property integer $manager_id
 *
 * @property Call $call
 * @property User $manager
 */
class CallManager extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'call_manager';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['call_id', 'manager_id'], 'required'],
            [['call_id', 'manager_id'], 'integer'],
            [['call_id'], 'exist', 'skipOnError' => true, 'targetClass' => Call::className(), 'targetAttribute' => ['call_id' => 'id']],
            [['manager_id'], 'exist', 'skipOnError' => true, 'targetClass' => User::className(), 'targetAttribute' => ['manager_id' => 'id']],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'call_id' => 'Call ID',
            'manager_id' => 'Manager ID',
        ];
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getCall()
    {
        return $this->hasOne(Call::className(), ['id' => 'call_id']);
    }

    /**
     * @return \yii\db\ActiveQuery
     */
    public function getManager()
    {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }
}
