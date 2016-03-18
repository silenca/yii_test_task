<?php

namespace app\models\forms;

use Yii;
use yii\base\Model;

/**
 * LoginForm is the model behind the login form.
 */
class CommentForm extends Model {
    
    public $comment;
    
    public function rules() {
        return [
            [['comment'], 'required', 'message' => '{attribute} не может быть пустым']
        ];
    }
    
    public function formName() {
        return '';
    }
    
    public function attributeLabels() {
        return [
            'comment' => 'Комментарий',
        ];
    }
}