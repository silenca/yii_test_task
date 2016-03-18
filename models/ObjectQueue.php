<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "object_queue".
 *
 * @property integer $id
 * @property integer $queue
 */
class ObjectQueue extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'object_queue';
    }

    public function getHouses() {
        return $this->hasMany(ObjectHouse::className(), ['queue_id' => 'id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['queue'], 'required'],
            #[['queue'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'queue' => 'Queue',
        ];
    }

//    public static function deleting($id) {
//        $objects_queue = self::find()->where(['id' => $id])->all();
//        if (count($objects_queue) == 1) {
//            $object_queue = $objects_queue[0];
//            if ($object_queue->delete()) {
//                return true;
//            } else {
//                return false;
//            }
//        }
//        return true;
//    }

}
