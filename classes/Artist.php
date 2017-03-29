<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 18.03
 * Time: 15:47
 */
class Artist
{
    /** @var Canvas $_canvas*/
    private $_canvas;
    /** @var Brush $_brush*/
    private $_brush;

    private $_changeCoordinates;
    private $_changeSize;
    private $_brushDown;

        /**
     * Artist constructor.
     * @param Canvas $canvas
     * @param null $brush
     * @throws Exception
     */
    public function __construct($canvas, $brush = null)
    {
        if (!$canvas) {
            throw new Exception('Художник не может быть без полотна.');
        }

        if (!$brush) {
            $brush = new Brush($canvas->getWidth() / 2, $canvas->getHeight() / 2);
        }

        $this->_canvas = $canvas;
        $this->_brush  = $brush;

        $this->_changeCoordinates = true;
        $this->_changeSize        = true;
        $this->_brushDown         = false;
    }

    /**
     * @param $dx
     * @param $dy
     * @param bool $changeState
     */
    public function moveBrush($dx, $dy, $changeState = true)
    {
        $brush = $this->_brush;
        $canvas = $this->_canvas;

        if (!empty($dx)) {
            $curX = $brush->getX();
            $cWidth = $canvas->getWidth();
            $newX = $curX + $dx;
            $newX = Utils::ifIsNan($newX, $curX);
            if ($newX < 0) $newX = $cWidth - 1;
            $brush->setX($newX % $cWidth);

            if ($changeState) {
                $this->_changeCoordinates = true;
                $this->_brushDown         = false;
            }
        }

        if (!empty($dy)) {
            $curY    = $brush->getY();
            $cHeight = $canvas->getHeight();
            $newY    = $curY + $dy;
            $newY    = Utils::ifIsNan($newY, $curY);
            if ($newY < 0) $newY = $cHeight - 1;
            $brush->setY($newY % $cHeight);

            if ($changeState) {
                $this->_changeCoordinates = true;
                $this->_brushDown         = false;
            }
        }
    }

    public function downBrush()
    {
        $canvas = $this->_canvas;
        $brush = $this->_brush;
        $brushState = new Brush($brush->getX(), $brush->getY(), $brush->getSize());

        $canvas->draw($brush);

        switch ($brush->getSize()) {
            case 2:
                foreach ([[1, 0], [0, 1], [1, 1]] as $deltas) {
                    $this->moveBrush($deltas[0], $deltas[1], false);
                    $canvas->draw($brush);
                }
                break;
            case 3:
                foreach ([[0, -1], [0, 1], [1, 0], [-1, 0]] as $deltas) {
                    $this->moveBrush($deltas[0], $deltas[1], false);
                    $canvas->draw($brush);
                }
                break;
        }
        $this->_brush = $brushState;

        // запоминаем что поставили тут точку с этими координами и этим размером
        $this->_changeCoordinates = false;
        $this->_changeSize        = false;
        $this->_brushDown         = true;
    }

    public function setColor($color)
    {
        $this->_brush->setColor($color);
    }

    /**
     * @param Operation $op
     */
    public function doOp($op)
    {
        $type = $op->getType();
        $params = $op->getParams();

        switch ($type) {
            case Operation::OT_MOVE:
                $dx = round($params['dx']);
                $dy = round($params['dy']);

                if (empty($dx) && empty($dy)) return;
                $this->moveBrush($dx, $dy);
                break;
            case Operation::OT_SET_COLOR:
                $color = $params['color'];
                if ($this->getColor() === $color) return;
                $this->setColor($color);
                $this->_brushDown = false;
                break;
            case Operation::OT_SET_SIZE:
                $size = $params['size'];
                if ($this->getSize() === $size) return;
                $this->setSize(round($size));
                $this->_changeSize = true;
                $this->_brushDown = false;
                break;
            case Operation::OT_DRAW:
                if ($this->_brushDown && !$this->_changeCoordinates && !$this->_changeSize) return;

                $this->downBrush();
                break;
        }
    }

    /**
     * @return Canvas
     */
    public function getCanvas()
    {
        return $this->_canvas;
    }

    private function getColor()
    {
        return $this->_brush->getColor();
    }

    private function getSize()
    {
        return $this->_brush->getSize();
    }

    private function setSize($size)
    {
        $this->_brush->setSize($size);
        $this->_changeSize = true;
    }
}