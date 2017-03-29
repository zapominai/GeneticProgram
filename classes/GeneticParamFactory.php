<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 19.03
 * Time: 12:59
 */
class GeneticParamFactory
{
    private $_params;

    /**
     * GeneticParamFactory constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->_params = $params;
    }

    public function getGen($type = null, $params = null) {
        if (is_null($type)) {
            $type = rand(0, count($this->_params) - 1);
        }

        $genSettings = $this->_params[$type];

        if (!$params) {
            $params = GeneticParam::fillParams($genSettings);
        }

        return new GeneticParam($type, $params, $genSettings);
    }

    public function generateRandom() {
        $result = [];

        for ($i = 0; $i < count($this->_params); $i++) {
            $result[] = self::getGen($i);
        }

        return $result;
    }

}