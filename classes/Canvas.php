<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 18.03
 * Time: 14:15
 */
class Canvas
{
    private $_width;
    private $_height;

    private $_data = [];

    /**
     * Canvas constructor.
     * @param $width
     * @param $height
     * @param array $_data
     */
    public function __construct($width, $height, $_data = [])
    {
        $this->_width  = $width;
        $this->_height = $height;

        if (!$_data) {
            foreach (range(0, $this->_height - 1) as $i) {
                foreach (range(0, $this->_width - 1) as $j) {
                    $this->_data[$i][$j] = 0;
                }
            }
        } else {
            $this->_data = $_data;
        }
    }

    /**
     * @return mixed
     */
    public function getWidth()
    {
        return $this->_width;
    }

    /**
     * @return mixed
     */
    public function getHeight()
    {
        return $this->_height;
    }

    /**
     * @param Brush $brush
     */
    public function draw($brush)
    {
        if ($this->_data[$brush->getY()][$brush->getX()] === $brush->getColor()) return;

        $this->_data[$brush->getY()][$brush->getX()] = $brush->getColor();
    }

    /**
     * @param Canvas $sample
     * @return float
     * @throws Exception
     */
    public function compare($sample) {
        if ($sample->getHeight() !== $this->getHeight() ||
            $sample->getWidth() !== $this->getWidth()
        ) {
            throw new Exception('Разные размеры холстов');
        }

        $errorCnt = 0;
        foreach ($this->getData() as $i => $rows) {
            $errorCnt += count(array_diff_assoc($rows, $sample->getData()[$i]));
        }

        $allPixels = $this->getHeight() * $sample->getWidth();

        return ($allPixels - $errorCnt) / $allPixels;
    }

    /**
     * @return array
     */
    public function getData()
    {
        return $this->_data;
    }

    public function save2image(&$image, $startX, $startY, $paddingTopLabel, $label, $pcBlack, $tColor)
    {
        $lineIndent = 10;

        foreach ($this->getData() as $i => $rows) {
            foreach ($rows as $j => $col) {
                if ($col !== 1) continue;

                imagesetpixel($image, $startX + $j, $startY + $i, $pcBlack);
            }
        }

        $labelX = $startX;
        $labelY = $startY + $this->getHeight() + $paddingTopLabel;

        $labels = explode(PHP_EOL, $label);

        foreach ($labels as $label) {
            imagestring($image, 1, $labelX, $labelY, $label, $tColor);
            $labelY += $lineIndent;
        }
    }
}