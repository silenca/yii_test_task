<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;
use app\models\User;

class TagsSelectWidget extends Widget
{
    public $tags;

    public function run()
    {
        $data = [];
        foreach ($this->tags as $i => $tag) {
            $data[$i]['id'] = $tag->id;
            $data[$i]['text'] = $tag->name;
            $data[$i]['description'] = $tag->description;
            $data[$i]['script'] = $tag->script;
            $data[$i]['as_task'] = $tag->as_task;
            $data[$i]['start_date'] = $tag->start_date;
            $data[$i]['end_date'] = $tag->end_date;
            foreach ($tag->users as $user) {
                switch (Yii::$app->user->identity->role) {
                    case User::ROLE_ADMIN:
                        if ($user->role != User::ROLE_ADMIN) {
                            $data[$i]['users'][] = $user->id;
                        }
                        break;
                    case User::ROLE_MANAGER:
                        if ($user->role != User::ROLE_ADMIN && $user->role != User::ROLE_MANAGER) {
                            $data[$i]['users'][] = $user->id;
                        }
                        break;
                    case User::ROLE_OPERATOR:
                        if ($user->role == User::ROLE_OPERATOR) {
                            $data[$i]['users'][] = $user->id;
                        }
                        break;
                }
            }

        }
        return $data;
    }
}
