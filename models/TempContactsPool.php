<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "temp_contacts_pool".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $manager_id
 * @property integer $tag_id
 */
class TempContactsPool extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'temp_contacts_pool';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id', 'manager_id', 'tag_id'], 'integer'],
        ];
    }

    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    public function getTag() {
        return $this->hasOne(Tag::className(), ['id' => 'tag_id']);
    }

    public static function updateOrderToken($contact_id, $operator_id, $tag_id, $call_order_token) {
        self::updateAll(['order_token' => $call_order_token], [
            'contact_id' => $contact_id,
            'manager_id' => $operator_id,
            'tag_id' => $tag_id,
        ]);
    }

    public static function clearForManagers($managers) {
        $managers_id = [];
        foreach ($managers as $manager) {
            $managers_id[] = $manager->id;
        }

        $temps = self::find()->where(['in', 'manager_id', $managers_id])->all();
        foreach ($temps as $temp) {
            $temp->delete();
        }
    }
    

//    public static function isExists($contact_id)
//    {
//        if (self::find()->where(['contact_id' => $contact_id])->exists()) {
//            return self::find()->where(['contact_id' => $contact_id])->one();
//        }
//        return false;
//    }
}
