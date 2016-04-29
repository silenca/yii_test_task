<?php

namespace app\components\widgets;

use app\components\Filter;
use yii\base\Widget;
use Yii;
//use app\models\Contact;
use app\models\Call;
use app\models\ActionType;

class TagContactsTableWidget extends Widget {
    public $tag_contacts;
    public $user_role;
    public $export;

    public function run() {
        $data = [];
        foreach ($this->tag_contacts as $i => $tag_contact) {
            $contact_phones = $tag_contact->phones;
//            $is_called_contact = $tag_contact->is_called;

            if ($tag_contact->is_called) {
                $phone_class = '';
            } else {
                $phone_class = 'contact-phone';
            }

            if (!$this->export) {
                $data[$i][] = $tag_contact->id;
            }
            $data[$i][] = $tag_contact->int_id;
            $data[$i][] = $tag_contact->surname;

            switch ($this->user_role) {
                case 'operator':
                    $phone_wrapper = '<a class="'.$phone_class.'" data-phone="{value_1}" href="javascript:void(0)">{value_2}</a>';
                    $data[$i][] = Filter::dataImplode([$tag_contact->getPhoneValues(), 'Телефон №'], ', ', $phone_wrapper, true, true);
                    break;
                default:
                    if (!is_null($this->export)) {
                        $data[$i][] = Filter::dataImplode($tag_contact->getPhoneValues());
                    } else {
                        $phone_wrapper = '<a class="'.$phone_class.'" data-phone="{value}" href="javascript:void(0)">{value}</a>';
                        $data[$i][] = Filter::dataImplode($tag_contact->getPhoneValues(), ', ', $phone_wrapper, true);
                    }
            }

            if ($tag_contact->is_called) {
                $manager = $contact_phones[0]->callManagers[0]->manager;
                if ($manager) {
                    $data[$i][] = $manager->firstname;
                } else {
                    $data[$i][] = '';
                }
            } else {
                $data[$i][] = '';
            }

            if ($tag_contact->is_called) {
                $call = $contact_phones[0];
                $data[$i][] = Call::getCallStatusLabel($call);

                $action_type = ActionType::find()->where(['name' => 'ring_round'])->one();
                $action = $tag_contact->getActions()->where(['action_type_id' => $action_type->id])->orderBy(['action.id' => SORT_DESC])->one();
                if ($action && $action->comment) {
                    $data[$i][] = $action->comment->comment;
                } else {
                    $data[$i][] = '';
                }

                $data[$i][] = Call::getCallAttitudeLabel($call);
            } else {
                $data[$i][] = '';
                $data[$i][] = '';
                $data[$i][] = '';
            }


        }
        return $data;
    }
}
