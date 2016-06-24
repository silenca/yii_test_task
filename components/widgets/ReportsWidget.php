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
    public $serveds;

    private $totalIncomingsSuccess = 0;
    private $totalIncomingsAll = 0;
    private $totalOutgoingsSuccess = 0;
    private $totalOutgoingsAll = 0;
    private $totalServeds = 0;

    public function init() {
        parent::init();
    }

    public function run() {
        $data = [];
        foreach ($this->users as $i => $user) {
            $data[$i][] = $user['firstname'];
            if (isset($this->incomings[$user['id']])) {
                $data[$i][] = $this->incomings[$user['id']]['success'] ."/".$this->incomings[$user['id']]['all'];
                $this->totalIncomingsSuccess += $this->incomings[$user['id']]['success'];
                $this->totalIncomingsAll += $this->incomings[$user['id']]['all'];
            } else {
                $data[$i][] = "0/0";
            }

            if (isset($this->outgoings[$user['id']])) {
                $data[$i][] = $this->outgoings[$user['id']]['success'] ."/".$this->outgoings[$user['id']]['all'];
                $this->totalOutgoingsSuccess += $this->outgoings[$user['id']]['success'];
                $this->totalOutgoingsAll += $this->outgoings[$user['id']]['all'];
            } else {
                $data[$i][] = "0/0";
            }

            if (isset($this->userCallTags[$user['id']])) {
                $data[$i][] = $this->userCallTags[$user['id']];
            } else {
                $data[$i][] = [];
            }
            if (isset($this->serveds[$user['id']])) {
                $data[$i][] = $this->serveds[$user['id']];
                $this->totalServeds += $this->serveds[$user['id']];
            } else {
                $data[$i][] = 0;
            }
        }
        $data[] = [
            "Итого:",
            $this->totalIncomingsSuccess . "/" . $this->totalIncomingsAll,
            $this->totalOutgoingsSuccess . "/" . $this->totalOutgoingsAll,
            [],
            $this->totalServeds
        ];
        return $data;
    }

}