<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;

class TagTableWidget extends Widget {
    public $tags;

    public function run() {
        $data = [];
        foreach ($this->tags as $i => $tag) {
            $data[$i][] = $tag->id;
            $data[$i][] = '<div class="tag-name">' . $tag->name . '</div>';
            $data[$i][] = '<div class="tag-description">' . $tag->description . '</div>';
            $data[$i][] = '<div class="col-md-offset-1 edit open-link" data-toggle="modal" data-target="#modalTagForm"><i class="fa fa-edit"></i></div>';
            $data[$i][] = '<div class="col-md-offset-1 remove open-link"><i class="fa fa-remove"></i></div>';
        }
        return $data;
    }
}
