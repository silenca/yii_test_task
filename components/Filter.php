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

    public static function strConverter($data, $cols) {
        switch (gettype($data)) {
            case 'string':

                break;
            case 'array':
                $data_res = [];
//                $db_cols = $callback();
                foreach ($cols as $col) {
                    if (isset($data[$col])) {
                        $data_res[] = $data[$col];
                    }
                }
                $data_res = implode(', ', $data_res);
                return $data_res;

                break;
            case 'object':

                break;
        }
    }

}
