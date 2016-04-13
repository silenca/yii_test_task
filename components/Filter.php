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

    public static function dataImplode($data, $glue = ', ', $wrapper = null, $partial = false)
    {
        $data = array_filter($data);
        if ($wrapper) {
            if ($partial) {
                $data = implode($glue, array_map(function($el) use($wrapper) {
                    return str_replace('{value}', $el, $wrapper);
                }, $data));

            } else {
                $data = implode($glue, $data);
                $data = str_replace('{value}', $data, $wrapper);
            }
        } else {
            $data = implode($glue, $data);
        }
        return $data;
    }

}
