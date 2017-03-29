<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 24.03
 * Time: 17:04
 */

class GeneticStrategy
{
    /** @var callable */
    public $doNextGen;
    private $_operationCountRange;
    private $_programCount;
    private $_operationCount;
    private $_successCanvas;
    private $_successPercent;
    private $_luckyPercent;
    private $_tournamentPartsSize;
    private $_gensParams;
    private $_genCountRange;

    /**
     * GeneticStrategy constructor.
     * @param $params
     */
    public function __construct($params)
    {
        $this->_programCount        = $params["programCount"];
        $this->_operationCount      = $params["operationCount"];
        $this->_operationCountRange = $params["operationCountRange"];
        $this->_successCanvas       = $params["successCanvas"];
        $this->_successPercent      = $params["successPercent"];
        $this->_luckyPercent        = $params["luckyPercent"];
        $this->_tournamentPartsSize = $params["tournamentPartsSize"];
        $this->_gensParams          = $params["gensParams"];
        $this->_maxGenCount         = $params["maxGenCount"];
        $this->_genCountRange       = $params["genCountRange"];
        $this->doNextGen            = $params["doNextGen"];
    }

    /**
     * @return mixed
     */
    public function getProgramCount()
    {
        return $this->_programCount;
    }

    /**
     * @return mixed
     */
    public function getOperationCount()
    {
        return $this->_operationCount;
    }

    /**
     * @return mixed
     */
    public function getSuccessCanvas()
    {
        return $this->_successCanvas;
    }

    /**
     * @return mixed
     */
    public function getSuccessPercent()
    {
        return $this->_successPercent;
    }

    /**
     * @return mixed
     */
    public function getLuckyPercent()
    {
        return $this->_luckyPercent;
    }

    /**
     * @return mixed
     */
    public function getTournamentPartsSize()
    {
        return $this->_tournamentPartsSize;
    }

    /**
     * @return mixed
     */
    public function getOperationCountRange()
    {
        return $this->_operationCountRange;
    }

    /**
     * @param mixed $operationCountRange
     */
    public function setOperationCountRange($operationCountRange)
    {
        $this->_operationCountRange = $operationCountRange;
    }

    /**
     * @return GeneticParamSettings[]
     */
    public function getGensParams()
    {
        return $this->_gensParams;
    }

    /**
     * @param mixed $gensParam
     */
    public function setGensParams($gensParam)
    {
        $this->_gensParams = $gensParam;
    }

    /**
     * @return mixed
     */
    public function getGenCountRange()
    {
        return $this->_genCountRange;
    }

    /**
     * @param mixed $genCountRange
     */
    public function setGenCountRange($genCountRange)
    {
        $this->_genCountRange = $genCountRange;
    }
}