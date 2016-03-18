<?php

namespace app\components;

class Filter {

    public static function isPositiveNumber($number) {
        if (is_numeric($number) && (int) $number > 0) {
            return true;
        }
        return false;
    }

    public static function length($value, $min, $max) {
        return strlen($value) >= $min && strlen($value) <= $max;
    }

}
