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
            $data[$i][] = '<div class="dropdown contact_open_disable">
                                <button class="btn btn-sm btn-default dropdown-toggle" href="javascript:void(0)" aria-expanded="true">Связать с...</button>
                            </div>';
            $phones = [];
            $emails = [];
            foreach ($contact as $contact_prop_key => $contact_prop_val) {
                if (preg_match('/(.*)_phone/', $contact_prop_key) && $contact_prop_val !== null) {
                    $phones[] = '<a class="contact-phone contact_open_disable" href="javascript:void(0)">' . $contact_prop_val . '</a>';
                } elseif (preg_match('/(.*)_email/', $contact_prop_key) && $contact_prop_val !== null) {
                    $emails[] = $contact_prop_val;
                }
            }
            $data[$i][] =  implode(', ', $phones);
            $data[$i][] = implode(', ', $emails);
            $tags = [];
            foreach ($contact->tags as $tag) {
                $tags[] = '<a class="contact_open_disable" href="javascript:void(0)">' . $tag->name . '</a>';
            }
            $data[$i][] = implode(', ', $tags);

            $data[$i][] = $contact->country;
            $data[$i][] = $contact->region;
            $data[$i][] = $contact->area;
            $data[$i][] = $contact->city;
            $data[$i][] = $contact->street;
            $data[$i][] = $contact->house;
            $data[$i][] = $contact->flat;

            if (Yii::$app->user->can('delete_contact')) {
                $data[$i][] = '<div class="col-md-offset-3 remove"><i class="fa fa-remove"></i></div>';
            } else {
                $data[$i][] = '';
            }

        }
        return $data;
    }

}
