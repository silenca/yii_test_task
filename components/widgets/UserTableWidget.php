<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;
use app\models\User;

class UserTableWidget extends Widget {

    public $users;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->users as $i => $user) {
            $data[$i][] = $user->id;
            $data[$i][] = $user->firstname . ' ' . $user->lastname . ' ' . $user->patronymic;
            switch ($user->role) {
                case User::ROLE_ADMIN:
                    $role = 'Администратор';
                    break;
                case User::ROLE_MANAGER:
                    $role = 'Менеджер';
                    break;
                case User::ROLE_OPERATOR:
                    $role = 'Оператор';
                    break;
                default:
                    $role = 'undefined';
            }
            $data[$i][] = $role;
            $data[$i][] = $user->int_id;
            $data[$i][] = $user->email;

            $tags = [];
            foreach ($user->tags as $tag) {
                $tags[] = '<a href="javascript:void(0)">' . $tag->name . '</a>';
            }
            $data[$i][] = implode(', ', $tags);

            if (Yii::$app->user->can('delete_user')) {
                $data[$i][] = '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>';
            } else {
                $data[$i][] = '';
            }

        }
        return $data;
    }

}
