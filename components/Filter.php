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

    public static function dataImplode($data, $glue = ', ', $wrapper = null, $partial = false, $multi_value = false)
    {
        $res_data = '';
        if ($multi_value) {
            $main_arr = $data[0];
            $cover_str = $data[1];
            $main_arr = array_values(array_filter($main_arr));
            if ($partial) {
                array_walk($main_arr, function(&$el, $i) use($wrapper, $cover_str) {
                    $res_str = str_replace('{value_1}', $el, $wrapper);
                    $el = str_replace('{value_2}', $cover_str.++$i, $res_str);
                });
                $wrapper = implode($glue, $main_arr);
            } else {
//                $data_value = implode($glue, $data_value);
//                $wrapper = str_replace('{value_'.$i.'}', $data_value, $wrapper);
            }
            $res_data = $wrapper;

            //-------------------------------------------------------------------------
//            $i = 1;
//            foreach ($data as &$data_value) {
//                if (is_array($data_value)) {
//                    $data_value = array_filter($data_value);
//                    if ($partial) {
//                        $wrapper = implode($glue, array_map(function($el) use($wrapper, $i) {
//                            return str_replace('{value_'.$i.'}', $el, $wrapper);
//                        }, $data_value));
//                    } else {
//                        $data_value = implode($glue, $data_value);
//                        $wrapper = str_replace('{value_'.$i.'}', $data_value, $wrapper);
//                    }
//                    $i++;
//                } else {
//                    $covers = [];
//                    $n = 1;
//                    foreach (array_filter($data[0]) as $main_el) {
//                        $covers[] = $data_value.$n;
//                        $n++;
//                    }
//                    if ($partial) {
//                        $wrapper = implode($glue, array_map(function($el) use($wrapper, $i) {
//                            return str_replace('{value_'.$i.'}', $el, $wrapper);
//                        }, $covers));
//
//                    } else {
//                        $covers = implode($glue, $covers);
//                        $wrapper = str_replace('{value_'.$i.'}', $covers, $wrapper);
//                    }
//                    $i++;
//                }
//            }
//            $res_data = $wrapper;

            //-------------------------------------------------------------------------
//            $i = 1;
//            foreach ($data as &$data_value) {
//                $data_value = array_filter($data_value);
//                if ($partial) {
//                    $wrapper = implode($glue, array_map(function($el) use($wrapper, $i) {
//                        return str_replace('{value_'.$i.'}', $el, $wrapper);
//                    }, $data_value));
//
//                } else {
//                    $data_value = implode($glue, $data_value);
//                    $wrapper = str_replace('{value_'.$i.'}', $data_value, $wrapper);
//                }
//                $i++;
//            }
//            $res_data = $wrapper;
        } else {
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
            $res_data = $data;
        }

        return $res_data;
    }

}
