<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 18.03
 * Time: 16:26
 */
class Program
{
    private $_id;
    private $_parent_id;
    private $_is_lucky;

    /** @var Operation[] $_operations */
    private $_operations;
    private $_success;
    private $_generation;
    /** @var  Canvas $_result */
    private $_result;

    /** @var  GeneticParam[] */
    private $_gens;

    /**
     * Program constructor.
     * @param array $_opsList
     * @param array $_gens
     * @param int $_generation
     */
    public function __construct($_opsList = [], $_gens = [], $_generation = 0)
    {
        $this->_operations = $_opsList;
        $this->_success = 0;
        $this->_result = null;
        $this->_generation = $_generation;
        $this->_gens = $_gens;
        $this->_parent_id = null;
        $this->_is_lucky = false;
    }

    /**
     * @param Artist $artist
     * @param Canvas $successCanvas
     */
    public function run($artist, $successCanvas)
    {
        /** @var Operation $op */
        foreach ($this->_operations as $op) {
            $artist->doOp($op);
        }

        $this->_result = $artist->getCanvas();
        $this->_success = $this->_result->compare($successCanvas);
    }

    /**
     * @return float
     */
    public function getSuccess()
    {
        return $this->_success;
    }

    /**
     * @param float $success
     */
    public function setSuccess($success)
    {
        $this->_success = $success;
    }

    /**
     * @return Operation[]
     */
    public function getOperations()
    {
        return $this->_operations;
    }

    /**
     * @param Operation[] $operations
     */
    public function setOperations($operations)
    {
        $this->_operations = $operations;
    }

    /**
     * @return mixed
     */
    public function getGeneration()
    {
        return $this->_generation;
    }

    /**
     * @param mixed $generation
     */
    public function setGeneration($generation)
    {
        $this->_generation = $generation;
    }

    public function save($generation)
    {
        $this->_generation = $generation;

        if (db::queryVal('
            SELECT count(*) FROM information_schema.tables WHERE table_schema = \'genetic_draw\' AND table_name = \'programs\' LIMIT 1;
            ') === '0') {
            db::query('
                USE `genetic_draw`;

                CREATE TABLE IF NOT EXISTS `programs` (
                  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
                  `parent_id` int(10) unsigned DEFAULT NULL,
                  `is_lucky` tinyint(3) unsigned DEFAULT \'0\',
                  `generation` int(10) unsigned NOT NULL DEFAULT \'0\',
                  `body` longblob NOT NULL,
                  `success` double unsigned NOT NULL DEFAULT \'0\',
                  `size` int(10) unsigned DEFAULT NULL,
                  `result` text NOT NULL,
                  `gens` mediumtext NOT NULL,
                  PRIMARY KEY (`id`),
                  KEY `generation` (`generation`),
                  KEY `success` (`success`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8;            
            ');
        }

        db::query('
            INSERT INTO `genetic_draw`.`programs` 
              (`parent_id`,`body`, `size`, `result`, `success`, `generation`, `gens`, `is_lucky`) VALUES 
              (:parent_id, :body, :size, :result, :success, :generation, :gens, :is_lucky);
        ', [
            ':body' => gzcompress(serialize($this->_operations), 2),
            ':size' => count($this->_operations),
            ':result' => serialize($this->_result),
            ':success' => $this->_success,
            ':generation' => $this->_generation,
            ':gens' => serialize($this->_gens),
            ':parent_id' => $this->_parent_id,
            ':is_lucky' => $this->_is_lucky ? 1 : 0,
        ]);

        $this->_id = db::lastId();
    }

    /**
     * @return Canvas
     */
    public function getResult()
    {
        return $this->_result;
    }

    /**
     * @return Operation[]
     */
    public function getId()
    {
        return $this->_id;
    }

    /**
     * @param Operation[] $id
     */
    public function setId($id)
    {
        $this->_id = $id;
    }

    public function load($programData)
    {
        $this->_generation = intval($programData->generation);
        $this->_operations = unserialize(gzuncompress($programData->body));
        $this->_result = unserialize($programData->result);
        $this->_success = floatval($programData->success);
        $this->_id = intval($programData->id);
        $this->_parent_id = intval($programData->parent_id);
        $this->_gens = unserialize($programData->gens);
        $this->_is_lucky = intval($programData->is_lucky) === 1 ? true : false;

        return $this;
    }

    /**
     * @return GeneticParam[]
     */
    public function getGens()
    {
        return $this->_gens;
    }

    /**
     * @param GeneticParam[] $gens
     */
    public function setGens($gens)
    {
        $this->_gens = $gens;
    }

    /**
     * @param GeneticStrategy $strategy
     * @return Program
     */
    public function getChild($strategy)
    {
        $operations = unserialize(serialize($this->_operations));
        $gens = unserialize(serialize($this->_gens));

        $child = new Program($operations, $gens, $this->_generation + 1);

        $child->setParentId($this->_id);
        $child->setIsLucky($this->getIsLucky());

        // Мутируем текущие операции
        $mutationMachine = new MutationMachine($child, $strategy);
        $mutatedChild = $mutationMachine->doMutation();
        return $mutatedChild;
    }

    public function getOpsCnt()
    {
        return count($this->_operations);
    }

    /**
     * @param Operation $op
     */
    public function addOperation($op)
    {
        $this->_operations[] = $op;
    }

    /**
     * @param mixed $parent_id
     */
    public function setParentId($parent_id)
    {
        $this->_parent_id = $parent_id;
    }

    public function getGensInfo()
    {
        /*$gensSummary = [];
        foreach ($this->getGens() as $gen) {
            $genType = $gen->getType();
            $curVal  = Utils::aVal($genType, $gensSummary, '-');

            if ($curVal !== '-') {
                $gensSummary[$genType] += $curVal;
            } else {
                $gensSummary[$genType] = '-';
            }
        }

        ksort($gensSummary);
        */

        $genOpsCnt = 0;
        $genGenCnt = 0;

        foreach ($this->getGens() as $gen) {
            if (in_array($gen->getType(), [GeneticParam::GPT_OPSAREA, GeneticParam::GPT_OPSVAR, GeneticParam::GPT_OPSCNT])) {
                $genOpsCnt++;
            } else {
                $genGenCnt++;
            }
        }

        return sprintf('%d:%d', $genOpsCnt, $genGenCnt);
    }

    /**
     * @return mixed
     */
    public function getIsLucky()
    {
        return $this->_is_lucky;
    }

    /**
     * @param mixed $is_lucky
     */
    public function setIsLucky($is_lucky)
    {
        $this->_is_lucky = $is_lucky;
    }

    public function deleteOperation($index)
    {
        unset($this->_operations[$index]);
    }

    public function addGen($gen)
    {
        $this->_gens[] = $gen;
    }

    public function deleteGen($index)
    {
        unset($this->_gens[$index]);
    }

    /**
     * @return array
     */
    public function getGensValues()
    {
        $values = [];
        foreach ($this->_gens as $gen) {
            if (!array_key_exists($gen->getType(), $values)) $values[$gen->getType()] = [];
            $values[$gen->getType()][] = $gen->getValue();
        }

        return array_map(function ($item) {
            sort($item);
            return $item;
        }, $values);
    }

    public function getOpsValues()
    {
        $values = [];
        foreach ($this->_operations as $op) {
            $values[] = [$op->getType(), $op->getParams()];
        }

        return $values;
    }
}