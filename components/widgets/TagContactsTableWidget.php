<?php

namespace app\components\widgets;

use app\components\Filter;
use yii\base\Widget;
use Yii;
use app\models\User;
//use app\models\Contact;
use app\models\Call;
use app\models\ActionType;

class TagContactsTableWidget extends Widget {
    public $tag_contacts;
    public $user_role;
    public $export;

    public function run() {
        $data = [];
        $i = 0;
        $ids = [];
        //$data[$j];
        foreach ($this->tag_contacts as $tag_contact) {
            if (!$this->export) {
                $data[$i][] = $tag_contact['id'];
            }
            if ($this->export && ($index = array_search($tag_contact['id'], $ids)) !== false) {
                $text = trim(preg_replace('/\s+/', ' ', $tag_contact['history_text']));
                $data[$index][count($data[$index]) - 1] .= "<p>".$text."</p>";
            } else {
                $ids[] = $tag_contact['id'];
                $data[$i][] = $tag_contact['int_id'];
                $data[$i][] = $tag_contact['name'] .' '. $tag_contact['surname'];
                switch ($this->user_role) {
                    case User::ROLE_OPERATOR:
                        $phone_wrapper = '<a class="contact-phone" data-phone="{value_1}" href="javascript:void(0)">{value_2}</a>';
                        $data[$i][] = Filter::dataImplode([$this->getPhones($tag_contact), 'Телефон №'], ', ', $phone_wrapper, true, true);
                        break;
                    default:
                        if (!is_null($this->export)) {
                            $data[$i][] = Filter::dataImplode($this->getPhones($tag_contact));
                        } else {
                            $phone_wrapper = '<a class="contact-phone" data-phone="{value}" href="javascript:void(0)">{value}</a>';
                            $data[$i][] = Filter::dataImplode($this->getPhones($tag_contact), ', ', $phone_wrapper, true);
                        }
                }
                if ($tag_contact['call_status']) {
                    $data[$i][] = $tag_contact['operator_name'];
                    $data[$i][] = Call::getCallStatusLabel($tag_contact['call_type'], $tag_contact['call_status']);
                    $data[$i][] = preg_replace('/\s\s+/', ' ', $tag_contact['call_comment']);
                    $data[$i][] = Call::getCallAttitudeLabel($tag_contact['call_attitude_level']);
                } else {
                    $data[$i][] = '';
                    $data[$i][] = '';
                    $data[$i][] = '';
                    $data[$i][] = '';
                }
                if ($this->export) {
                    $data[$i][] = $tag_contact['city'];
                    $data[$i][] = $tag_contact['street'];
                    $data[$i][] = $tag_contact['house'];
                    $data[$i][] = $tag_contact['flat'];
                    if ($tag_contact['history_text'] && $tag_contact['history_datetime']) {
                        $text = trim(preg_replace('/\s+/', ' ', $tag_contact['history_text']));
                        $data[$i][] .= "<p>".$tag_contact['history_datetime']." - ".$text."</p>";
                    }
                }
                $i++;
            }
        }
        return $data;
    }

    private function getPhones($tag_contact) {
        return [
            'first_phone' => $tag_contact['first_phone'],
            'second_phone' => $tag_contact['second_phone'],
            'third_phone' => $tag_contact['third_phone'],
            'fourth_phone' => $tag_contact['fourth_phone']
        ];
    }
}
