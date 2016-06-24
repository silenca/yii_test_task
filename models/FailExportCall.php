<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "fail_export_call".
 *
 * @property integer $id
 * @property integer $call_id
 * @property string $date_time
 */
class FailExportCall extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'fail_export_call';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['call_id', 'date_time'], 'required'],
            [['call_id'], 'integer'],
            [['date_time'], 'safe'],
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
            'date_time' => 'Date Time',
        ];
    }

    public static function add($call_id) {
        if (!FailExportCall::find()->where(['call_id' => $call_id])->exists()) {
            $fail_call = new FailExportCall();
            $fail_call->call_id = $call_id;
            $fail_call->date_time = date('Y-m-d H:i:s');
            return $fail_call->save();
        }
        return true;
    }

    public static function remove($call_id) {
        return self::deleteAll(['call_id' => $call_id]);
    }
}