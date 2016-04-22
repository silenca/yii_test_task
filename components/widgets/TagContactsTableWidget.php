<?php

namespace app\components\widgets;

use app\components\Filter;
use yii\base\Widget;
use Yii;
use app\models\Contact;

class TagContactsTableWidget extends Widget {
    public $tag_contacts;
    public $user_role;

    public function run() {
        $data = [];
        foreach ($this->tag_contacts as $i => $tag_contact) {
            $is_called_contact = !isset($tag_contact->int_id);
            if ($is_called_contact) {
                $contact = $tag_contact->contact;
                $phone_class = '';
            } else {
                $contact = $tag_contact;
                $phone_class = 'contact-phone';
            }

            $data[$i][] = $contact->id;
            $data[$i][] = $contact->int_id;
            $data[$i][] = $contact->surname;

            switch ($this->user_role) {
                case 'operator':
                    $phone_wrapper = '<a class="'.$phone_class.'" data-phone="{value_1}" href="javascript:void(0)">{value_2}</a>';
                    $data[$i][] = Filter::dataImplode([$contact->getPhoneValues(), 'Телефон №'], ', ', $phone_wrapper, true, true);
                    break;
                default:
                    $phone_wrapper = '<a class="'.$phone_class.'" data-phone="{value}" href="javascript:void(0)">{value}</a>';
                    $data[$i][] = Filter::dataImplode($contact->getPhoneValues(), ', ', $phone_wrapper, true);
            }
//            $phone_wrapper = '<a class="'.$phone_class.'" data-phone="{value}" href="javascript:void(0)">{value}</a>';
//            $data[$i][] = Filter::dataImplode($contact->getPhoneValues(), ', ', $phone_wrapper, true);

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
