<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "contact_tag".
 *
 * @property integer $id
 * @property integer $contact_id
 * @property integer $tag_id
 */
class ContactTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'contact_tag';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['contact_id', 'tag_id'], 'required'],
            [['contact_id', 'tag_id'], 'integer'],
        ];
    }
}
