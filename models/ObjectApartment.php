<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "object_apartment".
 *
 * @property integer $id
 * @property integer $floor_id
 * @property integer $apartment
 * @property string $area
 * @property string $status
 * @property integer $room_numbers
 * @property string $comment
 */
class ObjectApartment extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'object_apartment';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
//            [['floor_id', 'number', 'area', 'layout'], 'required'],
            #[['floor_id'], 'integer'],
            #[['area'], 'number'],
            #[['is_sold', 'comment', 'link', 'layout', 'number'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'floor_id' => 'Floor ID',
            'number' => 'Apartment Number',
            'area' => 'Area',
            'is_sold' => 'Status of object',
            'layout' => 'Room Numbers',
            'comment' => 'Comment',
        ];
    }
    
    public static function getApartmentByFloor($floor, $only_on_show = false) {
        $apartments = self::find()->select('id, number')->where(['floor_id' => $floor]);
        if ($only_on_show) {
            $apartments->andWhere(['is_sold' => '0']);
        }
        return $apartments->asArray()->all();
//        return self::find()->select('id, number')->where(['floor_id' => $floor])->asArray()->all();
    }
    
//    public static function deleteById($id) {
//        $object_apartment = self::find()->where(['id' => $id])->one();
//        if ($object_apartment) {
//            $floor_id = $object_apartment->floor_id;
//            if (ObjectFloor::deleting($floor_id)) {
//                return false;
//            }
//        }
//        return false;
//    }
}
