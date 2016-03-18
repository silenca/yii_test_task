<?php

namespace app\components;

use yii\db\Query;
use Yii;

class SessionHelper {

    const SESSION_DELIM = "|";

    public static function getOnlineUserIds() {
        $sessions = (new Query())->select('*')->from('session')->where('expire > :now', [
                    ':now' => time()
                ])->all();
        $user_ids = [];
        foreach ($sessions as $session) {
            $sessionData = Yii::$app->session->readSession($session['id']);
            $sessionUnserializedData = self::unserialize_session($sessionData);
            if (isset($sessionUnserializedData['__id'])) {
                $user_id = $sessionUnserializedData['__id'];
                $user_ids[] = $user_id;
            }
        }
        return $user_ids;
    }

    public static function unserialize_session($session_data, $start_index = 0, &$dict = null) {
        isset($dict) or $dict = array();

        $name_end = strpos($session_data, self::SESSION_DELIM, $start_index);

        if ($name_end !== FALSE) {
            $name = substr($session_data, $start_index, $name_end - $start_index);
            $rest = substr($session_data, $name_end + 1);

            $value = unserialize($rest);      // PHP will unserialize up to "|" delimiter.
            $dict[$name] = $value;

            return self::unserialize_session($session_data, $name_end + 1 + strlen(serialize($value)), $dict);
        }

        return $dict;
    }

}
