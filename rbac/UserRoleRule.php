<?php

namespace app\rbac;

use Yii;
use yii\rbac\Rule;
use yii\helpers\ArrayHelper;
use app\models\User;

class UserRoleRule extends Rule {

    public $name = 'userRole';

    public function execute($user, $item, $params) {
        //Получаем массив пользователя из базы
        $user = ArrayHelper::getValue($params, 'user', User::findOne($user));
        if ($user) {
            $role = $user->role; //Значение из поля role базы данных
            switch ($item->name) {
                case "admin":
                    return $role == User::ROLE_ADMIN;
                case "fin_dir":
                    return $role == User::ROLE_FIN_DIR;
                case "manager":
                    return $role == User::ROLE_MANAGER;
                case "supervisor":
                    return $role == User::ROLE_SUPERVISOR;
            }
        }
        return false;
    }

}
