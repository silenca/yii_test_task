<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_object".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $queue_id
 * @property integer $home_id
 * @property integer $floor_id
 * @property integer $apartment_id
 */
class ContactObject extends \yii\db\ActiveRecord {

    /**
     * @inheritdoc
     */
    public static function tableName() {
        return 'contact_object';
    }

    public function getApartment() {
        return $this->hasOne(ObjectApartment::className(), ['id' => 'apartment_id']);
    }

    /**
     * @inheritdoc
     */
    public function rules() {
        return [
            [['contact_id', 'object_id'], 'required'],
            [['contact_id', 'object_id'], 'integer'],
            [['schedule_date', 'system_date'], 'date', 'format' => 'yyyy-M-d H:m:s'],
            [['type','price'], 'string'],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels() {
        return [
            'id' => 'ID',
            'contact_id' => 'Contact ID',
            'queue_id' => 'Queue ID',
            'home_id' => 'Home ID',
            'floor_id' => 'Floor ID',
            'apartment_id' => 'Apartment ID',
        ];
    }

    public function add($contact_id, $apartment, $schedule_date = '', $type = 'show', $price = '') {
        $this->contact_id = $contact_id;
        $this->object_id = $apartment;
        $this->type = $type;
        $this->price = $price;
        if (strlen($schedule_date) > 0) {
            $this->schedule_date = date('Y-m-d G:i:s', strtotime($schedule_date));
        }
        $this->system_date = date('Y-m-d G:i:s', time());
        return $this->save();
    }

}
