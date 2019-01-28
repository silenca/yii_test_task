<?php
/**
 * Created by PhpStorm.
 * User: phobos
 * Date: 11/9/15
 * Time: 5:25 PM
 */

namespace app\components\widgets;


use yii\base\Widget;


class ReportsWidget extends Widget
{
    public $users;
    public $incomings;
    public $outgoings;
    public $userCallTags;
    public $contactsLeads;
    public $createVisits;
    public $leadInContacts;
    public $serveds;

    private $totalIncomingsSuccess = 0;
    private $totalIncomingsAll = 0;
    private $totalOutgoingsSuccess = 0;
    private $totalOutgoingsAll = 0;
    private $totalServeds = 0;
    private $totalContactsLeads = 0;
    private $totalCreateVisitsPending = 0;
    private $totalCreateVisitsTakePlace = 0;
    private $totalLeadInContacts = 0;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->users as $i => $user) {
            $data[$i][] = $user['firstname'];

            //Входящие звонки
            if (isset($this->incomings[$user['id']])) {
                $data[$i][] = $this->incomings[$user['id']]['success'] ."/".$this->incomings[$user['id']]['missed'];
                $this->totalIncomingsSuccess += $this->incomings[$user['id']]['success'];
                $this->totalIncomingsAll += $this->incomings[$user['id']]['missed'];
            } else {
                $data[$i][] = "0/0";
            }

            // Исходящие звонки
            if (isset($this->outgoings[$user['id']])) {
                $data[$i][] = $this->outgoings[$user['id']]['all'] ."/". $this->outgoings[$user['id']]['success'];
                $this->totalOutgoingsSuccess += $this->outgoings[$user['id']]['success'];
                $this->totalOutgoingsAll += $this->outgoings[$user['id']]['all'];
            } else {
                $data[$i][] = "0/0";
            }

            /*if (isset($this->userCallTags[$user['id']])) {
                $data[$i][] = $this->userCallTags[$user['id']];
            } else {
                $data[$i][] = [];
            }*/
            // Лиды
            if (isset($this->contactsLeads[$user['id']])) {
                $data[$i][] = $this->contactsLeads[$user['id']];
                $this->totalContactsLeads += $this->contactsLeads[$user['id']];
            } else {
                $data[$i][] = "0";
            }

            //Визиты
            if (isset($this->createVisits[$user['id']])) {
                $data[$i][] = $this->createVisits[$user['id']]['pending'] . "/" . $this->createVisits[$user['id']]['take_place'];
                $this->totalCreateVisitsPending += $this->createVisits[$user['id']]['pending'];
                $this->totalCreateVisitsTakePlace += $this->createVisits[$user['id']]['take_place'];
            } else {
                $data[$i][] = "0/0";
            }

            //Контакты , перещедших из статуса "Лид" в статус "Пациент"
            if (isset($this->leadInContacts[$user['id']])) {
                $data[$i][] = $this->leadInContacts[$user['id']];
                $this->totalLeadInContacts += $this->leadInContacts[$user['id']];
            } else {
                $data[$i][] = "0";
            }

            /*if (isset($this->serveds[$user['id']])) {
                $data[$i][] = $this->serveds[$user['id']];
                $this->totalServeds += $this->serveds[$user['id']];
            } else {
                $data[$i][] = 0;
            }*/
        }
        $data[] = [
            "Итого:",
            $this->totalIncomingsSuccess . "/" . $this->totalIncomingsAll,
            $this->totalOutgoingsAll . "/" . $this->totalOutgoingsSuccess,
            $this->totalContactsLeads,
            $this->totalCreateVisitsPending . "/" . $this->totalCreateVisitsTakePlace,
            $this->totalLeadInContacts
//            $this->totalServeds
        ];
        return $data;
    }

}