<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 18.03
 * Time: 16:28
 */
class OperationFactory
{
    public function getOp($type = null, $params = null) {
        $qOps = count(Operation::$ops) - 1;

        if (!$type) {
            do {
                $type = rand(0, $qOps);
                $skip = Utils::aVal('skip', Operation::$ops[$type], false);
            } while($skip);
        }

        if (!$params) {
            $params = self::fillParams(Operation::$ops[$type]);
        }

        return new Operation($type, $params);
    }

    public function generateRandom($quantity = 50) {
        $result = [];

        foreach (range(0, $quantity - 1) as $i) {
            $result[] = self::getOp();
        }

        return $result;
    }

    static function fillParams($paramsRules) {
        $params = [];
        $randomRange = false;
        $randomValues = false;

        if (array_key_exists('default', $paramsRules)) {
            $params = $paramsRules['default'];
        }

        if (array_key_exists('randomDelta', $paramsRules)) {
            $randomRange = $paramsRules['randomDelta'];
        }

        if (array_key_exists('randomValues', $paramsRules)) {
            $randomValues = $paramsRules['randomValues'];
        }

        foreach ($params as &$param) {
            if ($randomValues !== false) {
                $param = $randomValues[rand(0, count($randomValues) - 1)];
            }

            if ($randomRange !== false) {
                $param = $param + rand($randomRange[0], $randomRange[1]);
            }
        }

        return $params;
    }
}