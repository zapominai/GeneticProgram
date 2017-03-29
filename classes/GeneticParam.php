<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 19.03
 * Time: 12:22
 */
class GeneticParam
{
    const GPT_OPSAREA = 0; // Количество затрагиваемых мутацией операций в процентах
    const GPT_OPSVAR  = 1; // Сила мутации параметров оперций в процентах
    const GPT_OPSCNT  = 2; // Сила мутации количества операций в процентах
    const GPT_GPAREA  = 3; // Количество затрагиваемых параметров мутации в процентах
    const GPT_GPVAR   = 4; // Сила мутации параметров мутации в процентах
    const GPT_GPCNT   = 5; // Сила мутации количества параметров мутации

    private $_type;
    private $_params;
    /** @var GeneticParamSettings $_settings */
    private $_settings;

    /**
     * GeneticParam constructor.
     * @param $_type
     * @param $_params
     * @param GeneticParamSettings $_settings
     */
    public function __construct($_type, $_params, $_settings)
    {
        $this->_type   = $_type;
        $this->_params = $_params;
        $this->_settings = $_settings;
    }

    public static function fillParams($genSettings)
    {
        $params = [];
        $randomRange = false;
        $randomValues = false;

        /** @var GeneticParamSettings $genSettings */
        if (!is_null($genSettings->getDefault())) {
            $def = $genSettings->getDefault()["value"];
            $params["value"] = $def / ($genSettings->getInPercent() ? 100 : 1);
        }

        if (!is_null($genSettings->getRandomDelta())) {
            $randomRange = $genSettings->getRandomDelta();
        }

        if (!is_null($genSettings->getRandomValues())) {
            $randomValues = $genSettings->getRandomValues();
        }

        foreach ($params as &$param) {
            if ($randomValues !== false) {
                $param = $randomValues[rand(0, count($randomValues) - 1)];
            }

            if ($randomRange !== false) {
                $min = $randomRange[0];
                $max = $randomRange[1];

                $param = $param + rand($min, $max) / ($genSettings->getInPercent() ? 100 : 1);
            }
        }

        return $params;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return mixed
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @param mixed $params
     */
    public function setParams($params)
    {
        $this->_params = $params;
    }

    public function getMin()
    {
        $settings = $this->_settings;
        return $settings->getMin() / ($settings->getInPercent() ? 100 : 1);
    }

    public function getMax()
    {
        $settings = $this->_settings;
        return $settings->getMax() / ($settings->getInPercent() ? 100 : 1);
    }

    public function slice($value)
    {
        if ($value < $this->getMin()) $value = $this->getMin();
        if ($value > $this->getMax()) $value = $this->getMax();

        if (is_nan($value)) $value = $this->getMax();

        return $value;
    }

    public function getValue()
    {
        return $this->_params['value'];
    }
}