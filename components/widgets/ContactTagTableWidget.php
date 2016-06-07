<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;
use app\components\Filter;

class ContactTagTableWidget extends Widget
{

    public $contacts;
    public $contacts_tags;

    public function init()
    {
        parent::init();
    }

    public function run()
    {
        $data = [];
        foreach ($this->contacts as $i => $contact) {
            //            $data[$i][] = "<input type='checkbox' name='contacts[]' value='".$contact->id."'>";
            $data[$i][] = $contact['id'];
            $data[$i][] = $contact['int_id'];
            $data[$i][] = Filter::dataImplode(array_filter([$contact['surname'], $contact['name'], $contact['middle_name']]), ' ');
            //            $data[$i][] = $contact->surname;
            //            $data[$i][] = $contact->name;
            //            $data[$i][] = $contact->middle_name;
            $phones = [];
            $emails = [];
            foreach ($contact as $contact_prop_key => $contact_prop_val) {
                if ($contact_prop_val && !empty($contact_prop_val)) {
                    switch ($contact_prop_key) {
                        case 'first_phone':
                        case 'second_phone':
                        case 'third_phone':
                        case 'fourth_phone':
                            $phones[] = '<a class="contact-phone contact_open_disable" href="javascript:void(0)">' . $contact_prop_val . '</a>';
                            break;
                        case 'first_email':
                        case 'second_email':
                            $emails[] = $contact_prop_val;
                            break;
                    }
                }
            }
            $data[$i][] = implode(', ', $phones);
            $data[$i][] = implode(', ', $emails);
            $tags = [];
            $contactTags = array_filter($this->contacts_tags, function($item) use ($contact) {
                if ($item['contact_id'] == $contact['id'])
                    return $item;
            });

            foreach ($contactTags as $tag) {
                $tags[] = '<a class="contact-tags contact_open_disable" href="javascript:void(0)">' . $tag['tag_name'] . '</a>';
            }
            $data[$i][] = implode(', ', $tags);

            $data[$i][] = $contact['country'];
            $data[$i][] = $contact['region'];
            $data[$i][] = $contact['city'];
        }
        return $data;
    }

}
