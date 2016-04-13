<?php

namespace app\components\widgets;

use app\components\Filter;
use yii\base\Widget;
use Yii;
use app\models\Contact;

class TagContactsTableWidget extends Widget {
    public $tag_contacts;

    public function run() {
        $data = [];
        foreach ($this->tag_contacts as $i => $tag_contact) {
            $data[$i][] = $tag_contact->id;
            $data[$i][] = $tag_contact->int_id;
            $data[$i][] = $tag_contact->surname;
//            $phones = [];
//            foreach (Contact::getPhoneCols() as $col) {
//                $phones[] = $tag_contact->$col;
//            }
            $data[$i][] = Filter::dataImplode($tag_contact->getPhoneValues(), ', ', '<a class="contact-phone" href="javascript:void(0)">{value}</a>', true);
            $manager = $tag_contact->manager;
            if ($manager) {
                $data[$i][] = $manager->firstname;
            } else {
                $data[$i][] = '';
            }
            $data[$i][] = '';
            $data[$i][] = '';
            $data[$i][] = '';
        }
        return $data;
    }
}
