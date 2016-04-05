<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_comment".
 *
 * @property integer $id
 * @property integer $call_id
 * @property integer $attitude_level
 */
class CallAttitude extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'call_attitude';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['call_id', 'attitude_level'], 'required'],
            [['call_id', 'attitude_level'], 'integer'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'call_id' => 'Call ID',
            'attitude_level' => 'Call Attitude',
        ];
    }

    public function add($call_id, $attitude_level) {
        $this->call_id = $call_id;
        $this->attitude_level = $attitude_level;
        return $this->save();
    }

}
