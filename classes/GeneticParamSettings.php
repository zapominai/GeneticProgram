<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 26.03
 * Time: 12:11
 */
class GeneticParamSettings
{
    private $_default;
    private $_randomDelta;
    private $_range;
    private $_inPercent;
    private $_randomValues;

    /**
     * GeneticParamSettings constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->_default      = $params["default"];
        $this->_randomDelta  = $params["randomDelta"];
        $this->_randomValues = $params["randomValues"];
        $this->_range        = $params["range"];
        $this->_inPercent    = $params["inPercent"];
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->_default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default)
    {
        $this->_default = $default;
    }

    /**
     * @return mixed
     */
    public function getRandomDelta()
    {
        return $this->_randomDelta;
    }

    /**
     * @param mixed $randomDelta
     */
    public function setRandomDelta($randomDelta)
    {
        $this->_randomDelta = $randomDelta;
    }

    /**
     * @return mixed
     */
    public function getRange()
    {
        return $this->_range;
    }

    /**
     * @param mixed $range
     */
    public function setRange($range)
    {
        $this->_range = $range;
    }

    /**
     * @return mixed
     */
    public function getInPercent()
    {
        return $this->_inPercent;
    }

    /**
     * @param mixed $inPercent
     */
    public function setInPercent($inPercent)
    {
        $this->_inPercent = $inPercent;
    }

    /**
     * @return mixed
     */
    public function getRandomValues()
    {
        return $this->_randomValues;
    }

    /**
     * @param mixed $randomValues
     */
    public function setRandomValues($randomValues)
    {
        $this->_randomValues = $randomValues;
    }

    public function getMin()
    {
        return $this->getRange()[0];
    }

    public function getMax()
    {
        return $this->getRange()[1];
    }
}