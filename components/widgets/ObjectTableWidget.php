<?php

namespace app\components\widgets;

use Yii;
use yii\base\Widget;

class ObjectTableWidget extends Widget {

    public $objects;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->objects as $i => $object) {
            $data[$i][] = $object['id'];
            $data[$i][] = $object['link'];
            $data[$i][] = $object['queue'];
            $data[$i][] = $object['housing'];
            $data[$i][] = $object['floor'];
            $data[$i][] = $object['number'];
            $data[$i][] = $object['area'];
            if ($object['is_sold'] == '0') {
                $data[$i][] = 'На показе';
            } else {
                $data[$i][] = 'Продано';
            }
            $data[$i][] = $object['layout'];

            if (Yii::$app->user->can('edit_comment')) {
                $data[$i][] = '<div class="object-comment">' . $object['comment'] . '</div>' . '<button class="btn btn-primary btn-sm object-btn hide" data-toggle="modal" data-target="#modalEditComment">Редактировать</button>';
            } else {
                $data[$i][] = $object['comment'];
            }
        }
        return $data;
    }

}
