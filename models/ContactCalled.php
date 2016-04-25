<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_called".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $call_id
 * @property integer $manager_id
 */
class ContactCalled extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact_called';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id', 'call_id', 'manager_id'], 'integer'],
        ];
    }

    public function getContact() {
        return $this->hasOne(Contact::className(), ['id' => 'contact_id']);
    }

    public function getManager() {
        return $this->hasOne(User::className(), ['id' => 'manager_id']);
    }

    public function getCall() {
        return $this->hasOne(Call::className(), ['id' => 'call_id']);
    }
}
