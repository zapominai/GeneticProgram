<?php
/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 20.02.2016
 * Time: 18:44
 */

class Utils {
    // Возвращаем массив параметров
    // func_num_args(), func_get_args()
    public static function params2array($numargs, $arg_list, $skip = 1) {
        if ($numargs > $skip) {
            for ($i = 0; $i < $skip; $i++) {
                array_shift($arg_list);
            }
            return $arg_list;
        }

        return false;
    }

    public static function generatePass($length = 6) {
        $chars = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        return substr(str_shuffle($chars), 0, $length);
    }

    public static function aVal($key, $array, $defVal) {
        return array_key_exists($key, $array) ? $array[$key] : $defVal;
    }

    public static function isAjax() {
        return $_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest';
    }


    // Рекурсивно проходим все коллекции и массивы, применяя функцию.
    static function walk_collection(&$param, $func) {
        if (is_array($param)) {
            foreach ($param as $index => $value) {
                self::walk_collection($param[$index], $func);
            }
        } elseif (is_a($param, 'stdClass')) {
            foreach ($param as $index => $value) {
                self::walk_collection($value, $func);
                $param->$index = $value;
            }
        } else {
            if (gettype($param) !== 'boolean') {
                $param = $func($param);
            }
        }
    }

    static function ifIsNan($val, $isNanVal) {
        return is_nan($val) ? $isNanVal : $val;
    }

    public static function randByLimit($limit)
    {
        if (empty($limit)) return 0;

        if ($limit < 0) {
            $min = $limit;
            $max = 0;
        }

        if ($limit > 0) {
            $min = 0;
            $max = $limit;
        }

        return rand($min, $max);
    }
}

