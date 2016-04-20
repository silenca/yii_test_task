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
            $is_called_contact = !isset($tag_contact->int_id);
            if ($is_called_contact) {
                $contact = $tag_contact->contact;
            } else {
                $contact = $tag_contact;
            }

            $data[$i][] = $contact->id;
            $data[$i][] = $contact->int_id;
            $data[$i][] = $contact->surname;
            $data[$i][] = Filter::dataImplode($contact->getPhoneValues(), ', ', '<a class="contact-phone" href="javascript:void(0)">{value}</a>', true);
            if ($is_called_contact) {
                $manager = $tag_contact->manager;
                if ($manager) {
                    $data[$i][] = $manager->firstname;
                } else {
                    $data[$i][] = '';
                }
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
