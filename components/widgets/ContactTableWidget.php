<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;

class ContactTableWidget extends Widget {

    public $contacts;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->contacts as $i => $contact) {
            $data[$i][] = $contact->id;
            $data[$i][] = $contact->int_id;
            $data[$i][] = $contact->surname;
            $data[$i][] = $contact->name;
            $data[$i][] = $contact->middle_name;
            $data[$i][] = '';
            $phones = [];
            $emails = [];
            foreach ($contact as $contact_prop_key => $contact_prop_val) {
                if (preg_match('/(.*)_phone/', $contact_prop_key) && $contact_prop_val !== null) {
                    $phones[] = '<a href="javascript:void(0)">' . $contact_prop_val . '</a>';
                } elseif (preg_match('/(.*)_email/', $contact_prop_key) && $contact_prop_val !== null) {
                    $emails[] = $contact_prop_val;
                }
            }
            $data[$i][] =  implode(', ', $phones);
            $data[$i][] = implode(', ', $emails);
            $tags = [];
            foreach ($contact->tags as $tag) {
                $tags[] = '<a href="javascript:void(0)">' . $tag->name . '</a>';
            }
            $data[$i][] = implode(', ', $tags);
            $data[$i][] = '';
            if (Yii::$app->user->can('delete_contact')) {
                $data[$i][] = '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>';
            } else {
                $data[$i][] = '';
            }

        }
        return $data;
    }

}
