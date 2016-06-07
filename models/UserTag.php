<?php

namespace app\models;

use Yii;

/**
 * This is the model class for table "user_tag".
 *
 * @property integer $id
 * @property integer $user_id
 * @property integer $tag_id
 */
class UserTag extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return '{{%user_tag}}';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['user_id', 'tag_id'], 'required'],
            [['user_id', 'tag_id'], 'integer'],
        ];
    }
}
