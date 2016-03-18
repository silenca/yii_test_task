<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class SolutionForm extends Model {

    public $id;
    public $type;
    public $comment;

    public function rules() {
        return [
            [['id', 'type'], 'required'],
            [['id'], 'integer'],
            [['comment'], 'string', 'max' => 150],
            [['type'], 'in', 'range' => ['approved', 'revision', 'rejected']],
        ];
    }

    public function formName() {
        return '';
    }

    public function attributeLabels() {
        return [
            'type' => 'Тип решения',
        ];
    }

}
