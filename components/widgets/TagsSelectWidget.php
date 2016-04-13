<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;

class TagsSelectWidget extends Widget {
    public $tags;

    public function run() {
        $data = [];
        foreach ($this->tags as $i => $tag) {
            $data[$i]['id'] = $tag->id;
            $data[$i]['text'] = $tag->name;
            $data[$i]['description'] = $tag->description;
            $data[$i]['script'] = $tag->script;
            $data[$i]['as_task'] = $tag->as_task;
            $data[$i]['start_date'] = $tag->start_date;
            $data[$i]['end_date'] = $tag->end_date;
            $tag_users = [];
            foreach ($tag->users as $user) {
                $data[$i]['users'][] = $user->toArray();
                $tag_users[] = $user->id;
            }
            $tag_contacts = [];
            foreach ($tag->contacts as $contact) {
                $data[$i]['contacts'][] = $contact->toArray();
                $tag_contacts[] = $contact->id;
            }
            $data[$i]['tag_users'] = $tag_users;
            $data[$i]['tag_contacts'] = $tag_contacts;


//            $data[$i][] = '<div class="tag-description">' . $tag->description . '</div>';
//            $data[$i][] = '<div class="col-md-offset-1 edit open-link" data-toggle="modal" data-target="#modalTagForm"><i class="fa fa-edit"></i></div>';
//            $data[$i][] = '<div class="col-md-offset-1 remove open-link"><i class="fa fa-remove"></i></div>';
        }
        return $data;
    }
}
