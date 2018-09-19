<?php

namespace app\components;

use Yii;

class Notification {

    public static function incomingCall($params) {
        $host = self::getHost('incoming');
        $request_params = self::buildParams($params);
        $curl = curl_init($host);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
    }

    public static function closeCall($params) {
        $host = self::getHost('close_call');
        $request_params = self::buildParams($params);
        $curl = curl_init($host);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
    }

    public static function newContract($params) {
        $host = self::getHost('contract');
        $request_params = self::buildParams($params);
        $curl = curl_init($host);
        curl_setopt($curl, CURLOPT_POST, 1);
        curl_setopt($curl, CURLOPT_POSTFIELDS, $request_params);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_exec($curl);
    }

    private static function buildParams($params) {
        $request_params = "";
        if (count($params) > 0) {
            foreach ($params as $name => $value) {
                $request_params.= $name.="=" . $value . "&";
            }
            $request_params = rtrim($request_params, "&");
        }
        return $request_params;
    }

    private static function getHost($resource) {
        switch($resource) {
            case "incoming":
                return Yii::$app->params['host_notify_incoming'];
            case "contract":
                return Yii::$app->params['host_notify_contract'];
            case "close_call":
                return Yii::$app->params['host_notify_close_call'];
        }
    }

}
