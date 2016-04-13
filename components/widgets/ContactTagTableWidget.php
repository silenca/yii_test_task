<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;
use app\components\Filter;

class ContactTagTableWidget extends Widget {

    public $contacts;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->contacts as $i => $contact) {
            $data[$i][] = $contact->id;
            $data[$i][] = $contact->int_id;
            $data[$i][] = Filter::dataImplode(array_filter([$contact->surname, $contact->name, $contact->middle_name]), ' ');
//            $data[$i][] = $contact->surname;
//            $data[$i][] = $contact->name;
//            $data[$i][] = $contact->middle_name;
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
            $data[$i][] = $contact->city;
            $data[$i][] = "<input type='checkbox' name='contacts[]' value='".$contact->id."'>";
        }
        return $data;
    }

}
