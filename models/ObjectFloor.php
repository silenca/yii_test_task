<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "object_floor".
 *
 * @property integer $id
 * @property integer $house_id
 * @property integer $floor
 */
class ObjectFloor extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'object_floor';
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['house_id', 'floor'], 'required'],
            #[['house_id'], 'integer'],
            #[['floor'], 'string']
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'house_id' => 'House ID',
            'floor' => 'Floor',
        ];
    }

    public static function getFloorByHouse($house) {
        return self::find()->select('id, floor')->where(['house_id' => $house])->asArray()->all();
    }

//    public static function deleting($id) {
//        $objects_floor = self::find()->where(['id' => $id])->all();
//        if (count($objects_floor) == 1) {
//            $object_floor = $objects_floor[0];
//            $house_id = $object_floor->house_id;
//            if (ObjectHouse::deleting($house_id) && $object_floor->delete()) {
//                return true;
//            } else {
//                return false;
//            }
//        }
//        return true;
//    }

}
