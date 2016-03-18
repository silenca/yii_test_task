<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "object_house".
 *
 * @property integer $id
 * @property integer $queue_id
 * @property string $housing
 */
class ObjectHouse extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'object_house';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['queue_id', 'housing'], 'required'],
            [['queue_id'], 'integer'],
            [['housing'], 'string', 'max' => 10],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'queue_id' => 'Queue ID',
            'housing' => 'Housing',
        ];
    }

    public static function getHouseByQueue($queue) {
        return self::find()->select('id, housing')->where(['queue_id' => $queue])->asArray()->all();
    }

//    public static function deleting($id) {
//        $objects_house = self::find()->where(['id' => $id])->all();
//        if (count($objects_house) == 1) {
//            $object_house = $objects_house[0];
//            $queue_id = $object_house->queue_id;
//            if (ObjectQueue::deleting($queue_id) && $object_house->delete()) {
//                return true;
//            } else {
//                return false;
//            }
//        }
//        return true;
//    }

}
