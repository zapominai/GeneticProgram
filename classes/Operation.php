<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 18.03
 * Time: 16:17
 */
class Operation
{
    const OT_MOVE = 0;
    const OT_DRAW = 1;
    const OT_SET_COLOR = 2;
    const OT_SET_SIZE = 3;

    static $ops = [
        self::OT_MOVE => ["default" => ["dx" => 1, "dy" => 1], "randomDelta" => [-2, 2]],
        self::OT_DRAW => [],
        self::OT_SET_COLOR => ["default" => ["color" => 1], "randomValues" => [0, 1], "skip" => true],
        self::OT_SET_SIZE => ["default" => ["size" => 1], "randomValues" => [1, 2, 3]],
    ];

    private $_type;
    private $_params;

    /**
     * Operation constructor.
     * @param $_type
     * @param $_params
     */
    public function __construct($_type, $_params = [])
    {
        $this->_type   = $_type;
        $this->_params = $_params;
    }

    /**
     * @return mixed
     */
    public function getType()
    {
        return $this->_type;
    }

    /**
     * @return array
     */
    public function getParams()
    {
        return $this->_params;
    }

    /**
     * @param array $params
     */
    public function setParams($params)
    {
        $this->_params = $params;
    }
}

