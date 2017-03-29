<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 18.03
 * Time: 15:42
 */
class Brush
{
    const CL_WHITE = 0;
    const CL_BLACK = 1;

    private $_x;
    private $_y;
    private $_size;

    private $_color;

    /**
     * Brush constructor.
     * @param int $_x
     * @param int $_y
     * @param int $_size
     * @param int $_color
     */
    public function __construct($_x = 0, $_y = 0, $_size = 1, $_color = self::CL_BLACK)
    {
        $this->_x     = $_x;
        $this->_y     = $_y;
        $this->_size  = $_size;
        $this->_color = $_color;
    }

    /**
     * @return int
     */
    public function getX()
    {
        return $this->_x;
    }

    /**
     * @return int
     */
    public function getY()
    {
        return $this->_y;
    }

    /**
     * @param int $x
     */
    public function setX($x)
    {
        $this->_x = $x;
    }

    /**
     * @param int $y
     */
    public function setY($y)
    {
        $this->_y = $y;
    }

    /**
     * @param int $size
     */
    public function setSize($size)
    {
        $this->_size = $size;
    }

    /**
     * @param int $color
     */
    public function setColor($color)
    {
        $this->_color = $color;
    }

    /**
     * @return int
     */
    public function getColor()
    {
        return $this->_color;
    }

    /**
     * @return int
     */
    public function getSize()
    {
        return $this->_size;
    }


}