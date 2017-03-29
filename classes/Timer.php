<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 24.03
 * Time: 20:22
 */
class Timer
{
    private $_name;
    private $_time;

    /**
     * Timer constructor.
     * @param string $name
     */
    public function __construct($name = '')
    {
        $this->_time = microtime(true);
        $this->_name = $name;
    }

    /**
     * @return string
     */
    public function getName()
    {
        return $this->_name;
    }

    /**
     * @return mixed
     */
    public function getTime()
    {
        return $this->_time;
    }

    public function stop() {
        $this->_time = microtime(true) - $this->_time;
        return $this->_time;
    }

}