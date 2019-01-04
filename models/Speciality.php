<?php

namespace app\models;

/**
 * This is the model class for table "speciality".
 *
 * @property integer $id
 * @property string $title
 * @property string $oid
 */
class Speciality extends \yii\db\ActiveRecord
{
    /**
     * @inheritdoc
     */
    public static function tableName()
    {
        return 'speciality';
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['title', 'oid'], 'required'],
            [['title'], 'string', 'max' => 50],
            [['oid'], 'string', 'max' => 15],
        ];
    }

    /**
     * @inheritdoc
     */
    public function attributeLabels()
    {
        return [
            'id' => 'ID',
            'title' => 'Title',
            'oid' => 'Oid',
        ];
    }

    public static function import($data): bool
    {
        if(!empty($data['name']) && !empty($data['oid'])) {
            $specialization = self::find()->where(['oid' => $data['oid']])->one();
            if($specialization){
                $specialization->title = $data['name'];
                if($specialization->save()){
                    return true;
                }else{
                    return false;
                }
            }else{
                $model = new Speciality();
                $model->title = $data['name'];
                $model->oid = $data['oid'];
                if($model->save()){
                    return true;
                }else{
                    return false;
                }
            }
        }else{
            return false;
        }
    }
}
