<?php

namespace app\components\widgets;

use yii\base\Widget;
use Yii;

class CallTableWidget extends Widget {

    public $calls;
    public $calls_missed;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        $missed_calls = [];
        $existing = [];
        $i = 0;
        foreach($this->calls_missed as $call_missed) {
            $missed_calls[] = $call_missed['call_id'];
        }
        foreach ($this->calls as $call) {
            if (($index = array_search($call['id'], $existing)) !== false) {
                $data[$index][4] .= "<span>, ".$call['manager']."</span>";
            } else {
                $call_success = true;
                $existing[$i] = $call['id'];
                $data[$i][] = $call['id'];
                if (array_search($call['id'], $missed_calls) === false) {
                    $data[$i][] = 0;
                } else {
                    $data[$i][] = 1;
                }
                $data[$i][] = $call['date'];
                $data[$i][] = $call['time'];
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
                $data[$i][] = "<span>".$call['manager']."</span>";
                if ($call['contact_id']) {
                    $contact_href = "contact=" . $call['contact_id'];
                    $contact_name = $call['contact'];
                } else {
                    $contact_href = "number=" . $call['phone_number'];
                    $contact_name = $call['phone_number'];
                }
                $data[$i][] = "<a class='contact' href='/contacts#" . $contact_href . "'>" . $contact_name . "</a>";
//                $phones[] = '<a class="contact-phone contact_open_disable" href="javascript:void(0)">' . $contact_prop_val . '</a>';
                if (Yii::$app->user->can('listen_call')) {
                    if ($call_success) {
                        $data[$i][] = "<audio controls src='" . $call["record"] . "' type='audio/mpeg'>";
                    } else {
                        $data[$i][] = "";
                    }
                }
                $i++;
            }
        }
        return $data;
    }

}
