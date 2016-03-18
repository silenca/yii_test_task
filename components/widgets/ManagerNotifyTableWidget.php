<?php

namespace app\components\widgets;

use yii\base\Widget;

class ManagerNotifyTableWidget extends Widget {

    public $notifications;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->notifications as $i => $notification) {
            $data[$i][] = $notification['id'];
            $data[$i][] = date("d-m-Y G:i", strtotime($notification['system_date']));
            switch ($notification['type']) {
                case "visit":
                    $data[$i][]['type'] = "Запланирован визит";
                    break;
                case "show":
                    $data[$i][]['type'] = "Запланирован показ объекта";
                    break;
                case "call_missed":
                    $data[$i][]['type'] = "Пропущенный звонок";
                    break;
                case "scheduled_call":
                    $data[$i][]['type'] = "Запланирован звонок";
                    break;
                case "scheduled_email":
                    $data[$i][]['type'] = "Запланирован Email";
                    break;
                case "jivosite":
                    $data[$i][]['type'] = "JivoSite";
                    break;
                case "fin_dir":
                    $data[$i][]['type'] = "От фин. директора";
                    break;
                case "contract_approved":
                    $data[$i][]['type'] = "Договор подтвержден";
                    break;
                case "contract_revision":
                    $data[$i][]['type'] = "Договор отправлен на доработку";
                    break;
                case "contract_rejected":
                    $data[$i][]['type'] = "Договор отклонен";
                    break;
            }

            if ($notification['action_schedule_date'] != null) {
                end($data[$i]);
                $last_id = key($data[$i]);
                $data[$i][$last_id]['schedule_date'] = $notification['action_schedule_date'];
            }
            
            if ($notification['contact_id']) {
                $contact_name = '';
                if ($notification['first_name']) {
                    $contact_name = $notification['first_name'];
                } else {
                    $contact_name = $notification['second_name'];
                }
                $data[$i][] = "<a class='contact' href='/contacts#contact=" . $notification['contact_id'] . "/'>" . $contact_name . "</a>";
            } elseif ($notification['phone_number']) {
                $data[$i][] = "<a class='contact' href='/contacts#number=" . $notification['phone_number'] . "/'>" . $notification['phone_number'] . "</a>";
            } else {
                $data[$i][] = "<a class='contact' href='/managernotify'>Контакт неопределён</a>";
            }
            $data[$i][] = $notification['jivosite_message'];
            $data[$i][] = $notification['comment'];
            $data[$i][] = $notification['viewed'];
        }
        return $data;
    }

}
