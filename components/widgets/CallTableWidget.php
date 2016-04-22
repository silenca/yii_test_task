<?php

namespace app\components\widgets;

use app\components\Filter;
use yii\base\Widget;
use Yii;

class CallTableWidget extends Widget {

    public $calls;
    public $user_role;
//    public $calls_missed;

//    public function init() {
//        parent::init();
//    }

    public function run() {
        $data = [];
//        $missed_calls = [];
//        $existing = [];
//        $i = 0;
//        foreach($this->calls_missed as $call_missed) {
//            $missed_calls[] = $call_missed['call_id'];
//        }
        foreach ($this->calls as $i => $call) {
//            if (($index = array_search($call['id'], $existing)) !== false) {
//                $data[$index][4] .= "<span>, ".$call['manager']."</span>";
//            } else {

            $call_missed = $call->missedCall;
            $call_contact = $call->contact;
            $call_managers = $call->callManagers;

            $call_success = true;
//                $existing[$i] = $call['id'];
            $data[$i][] = $call['id'];
            if ($call_missed) {
                $data[$i][] = 1;
            } else {
                $data[$i][] = 0;
            }
            $data[$i][] = date('d-m-Y', strtotime($call['date_time']));
            $data[$i][] = date('H-i-s', strtotime($call['date_time']));
            switch ($call['status']) {
                case "answered":
                    switch ($call['type']) {
                        case "incoming":
                            $data[$i][] = "Исходящий";
                            break;
                        case "outgoing":
                            $data[$i][] = "Входящий";
                            break;
                    }
                    break;
                case "missed":
                    $call_success = false;
                    switch ($call['type']) {
                        case "incoming":
                            $data[$i][] = "Исходящий";
                            break;
                        case "outgoing":
                            $data[$i][] = "Пропущенный";
                            break;
                    }
                    break;
                case "failure":
                    $call_success = false;
                    switch ($call['type']) {
                        case "incoming":
                            $data[$i][] = "Исходящий - сбой";
                            break;
                        case "outgoing":
                            $data[$i][] = "Входящий - сбой";
                            break;
                    }
                    break;
            }
            $managers = [];
            foreach ($call_managers as $call_manager) {
                $managers[] = $call_manager->manager->firstname;
            }
            $data[$i][] = Filter::dataImplode($managers, ', ');

            switch ($this->user_role) {
                case 'operator':
                    if ($call['contact_id']) {
                        $contact_name = $call_contact->int_id;
                    } else {
                        $contact_name = 'Телефон';
                    }
                    break;
                default:
                    if ($call['contact_id']) {
                        $contact_name = $call_contact->name;
                    } else {
                        $contact_name = $call['phone_number'];
                    }
            }
            $data[$i][] = "<a class='contact' data-contact_id='".$call['contact_id']."' data-phone='".$call['phone_number']."' href='javascript:void(0)'>" . $contact_name . "</a>";

            if (Yii::$app->user->can('listen_call')) {
                if ($call_success) {
                    $data[$i][] = "<audio controls src='" . $call["record"] . "' type='audio/mpeg'>";
                } else {
                    $data[$i][] = "";
                }
            }
//                $i++;
//            }
        }

        return $data;
    }

}
