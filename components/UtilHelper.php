<?php

namespace app\components;

class UtilHelper {

    public static function getRandomNumbers($length) {
        return substr(number_format(time() * rand(), 0, '', ''), 0, $length);
    }

    public static function pluralForm($n, $form1, $form2, $form3) {
        $n = abs($n) % 100;
        $n1 = $n % 10;
        if ($n > 10 && $n < 20)
            return $form3;
        if ($n1 > 1 && $n1 < 5)
            return $form2;
        if ($n1 == 1)
            return $form1;
        return $form3;
    }

}
