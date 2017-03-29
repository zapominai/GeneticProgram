<?php

/**
 * Created by PhpStorm.
 * User: ivan
 * Date: 27.02.16
 * Time: 16:12
 */
class log
{
    public static function write($data, $context = '') {
        $logDir = dirname(__FILE__) . '/../log';

        if (!is_dir($logDir)) {
            mkdir($logDir);
        }

        $logFile = sprintf($logDir . '/%s.log', date('Y-m-d'));

        $file = fopen($logFile, 'a+');
        flock($file, LOCK_EX);

        fwrite(
            $file,
            sprintf('%s | %s | %s' . PHP_EOL, date('d.m.Y H:i:s'), $context, $data)
        );

        flock($file, LOCK_UN);
        fclose($file);
    }

    public static function error() {
        $params = func_get_args();
        log::write('ERROR: ' . call_user_func_array('sprintf', $params));
    }

    public static function errorWithRequest() {
        $params = func_get_args();
        $message = 'ERROR: ' . call_user_func_array('sprintf', $params);
        $message .= sprintf(' _REQUEST(%s => %s)', implode(', ', array_keys($_REQUEST)), implode(', ', $_REQUEST));

        log::write($message);
    }

    public static function debug() {
        $params = func_get_args();
        log::write(call_user_func_array('sprintf', $params));
    }
}