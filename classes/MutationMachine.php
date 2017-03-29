<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 26.03
 * Time: 14:10
 */
class MutationMachine
{
    static $mutationStateCalcers;
    /** @var MutationState $_mutationState */
    private $_mutationState;
    /** @var Program $_child */
    private $_child;
    /** @var GeneticStrategy $_strategy */
    private $_strategy;

    /**
     * MutationMachine constructor.
     * @param Program $child
     * @param $strategy
     */
    public function __construct($child, $strategy)
    {
        $this->_child         = $child;
        $this->_strategy      = $strategy;
        $this->_mutationState = new MutationState();
    }

    /**
     * @return Program
     */
    public function doMutation()
    {
        $child = $this->_child;
        $mutationState = $this->_mutationState;

        foreach ($child->getGens() as $gen) {
            $mutationStateCalcer = self::$mutationStateCalcers[$gen->getType()];
            $mutationStateCalcer($gen, $child, $mutationState);
        }

        // Мутации количества операций, параметров генома и размеров генома
        $this->doOpsParamMutation();
        $this->doOpsCntMutation();
        $this->doGensParamMutation();
        $this->doGensCntMutation();

        return $child;
    }

    private function doOpsCntMutation()
    {
        $child = $this->_child;
        $mutationState = $this->_mutationState;
        /** @var GeneticStrategy $strategy */
        $strategy = $this->_strategy;

        if (empty($mutationState->opCntDelta)) return;

        $programCntRange = $strategy->getOperationCountRange();

        $doChangeOpsCnt = true;
        if ($programCntRange) {
            if ($mutationState->opCntDelta > 0 && $child->getOpsCnt() > $programCntRange[1]) {
                $doChangeOpsCnt = false;
            }

            if ($mutationState->opCntDelta < 0 && $child->getOpsCnt() < $programCntRange[0]) {
                $doChangeOpsCnt = false;
            }
        }

        if (!$doChangeOpsCnt) return;

        for($i = 0; $i < $mutationState->opCntDelta; $i++) {
            if ($mutationState->opCntDelta > 0) {
                $child->addOperation((new OperationFactory())->getOp());
            } else {
                $child->deleteOperation(rand(0, $child->getOpsCnt() - 1));
            }
        }
    }

    private function doGensParamMutation()
    {
        $child = $this->_child;
        $mutationState = $this->_mutationState;
        $strategy = $this->_strategy;

        $qGens   = count($child->getGens());
        $qChangeGens = $mutationState->gpArea;
        $gensKey = array_keys($child->getGens());
        shuffle($gensKey);

        if (empty($qChangeGens)) return;

        if ($qChangeGens > $qGens) $qChangeGens = $qGens;

        for ($i = 0; $i < $qChangeGens; $i++) {
            // может позволить брать один и тот же? array_rand()
            $gen = $child->getGens()[$gensKey[$i]];

            $genValue = $gen->getValue();
            $sign = rand(0, 1) === 0 ? -1 : 1;
            $delta = Utils::randByLimit($mutationState->gpVar * 100) * $sign;

            $newValue = $gen->slice($genValue + ($delta / 100) * $genValue);
            $gen->setParams(['value' => $newValue]);
        }
    }

    private function doGensCntMutation()
    {
        $child = $this->_child;
        $mutationState = $this->_mutationState;
        $strategy = $this->_strategy;

        $gensKey = array_keys($child->getGens());
        shuffle($gensKey);

        if (empty($mutationState->gpCntDelta)) return;

        if ($mutationState->gpCntDelta > 0) {
            if ((count($child->getGens()) + $mutationState->gpCntDelta) < $strategy->getGenCountRange()[1]) {
                for ($i = 0; $i < $mutationState->gpCntDelta; $i++) {
                    $child->addGen((new GeneticParamFactory($strategy->getGensParams()))->getGen());
                }
            }
        } else {
            if ((count($child->getGens()) - abs($mutationState->gpCntDelta)) > $strategy->getGenCountRange()[0]) {
                for ($i = 0; $i < abs($mutationState->gpCntDelta); $i++) {
                    $child->deleteGen($gensKey[$i]);
                }
            }
        }

    }

    private function doOpsParamMutation()
    {
        $child = $this->_child;
        $mutationState = $this->_mutationState;
        $strategy = $this->_strategy;

        $qOps = $child->getOpsCnt();
        $qMutOps = $mutationState->opArea;
        $deltaValue = $mutationState->opVar;
        $deltaMaxValue = $strategy->getGensParams()[GeneticParam::GPT_GPVAR]->getMax() / 100;

        if (empty($qMutOps)) return;

        if ($qMutOps > $qOps) {
            $qMutOps = $qOps;
        }

        $ops = $child->getOperations();
        $opsKeys = array_keys($ops);
        shuffle($opsKeys);

        for ($i = 0; $i < $qMutOps; $i++) {
            /** @var Operation $operation */
            $operation = $ops[$opsKeys[$i]];
            $opParams = $operation->getParams();

            switch ($operation->getType()) {
                case Operation::OT_SET_COLOR:
                    if (($deltaValue > $deltaMaxValue / 2) && rand(0, 10) === 0) {
                        $operation->setParams(['color' => $opParams['color'] === 1 ? 0 : 1]);
                    }
                    break;
                case Operation::OT_SET_SIZE:
                    $curSize = $opParams['size'];

                    $sign = rand(0, 1) === 0 ? -1 : 1;
                    $delta = $curSize * $deltaValue;

                    if ($curSize > 1) {
                        $delta *= $sign;
                    }

                    $newSize = Utils::ifIsNan($curSize + $delta, $curSize);

                    if ($newSize < 0) $newSize = 1;
                    if ($newSize > 3) $newSize = 3;

                    if ($curSize !== $newSize) {
                        $operation->setParams(['size' => $newSize]);
                    }
                    break;
                case Operation::OT_MOVE:
                    //$dx = empty($opParams['dx']) ? .1 : $opParams['dx'];
                    //$dy = empty($opParams['dy']) ? .1 : $opParams['dy'];
                    $dx = $opParams['dx'];
                    $dy = $opParams['dy'];

                    if (empty($dx) && empty($dy)) continue;

                    $signX = rand(0, 1) === 0 ? -1 : 1;
                    $signY = rand(0, 1) === 0 ? -1 : 1;

                    $deltaX = $dx * (Utils::randByLimit($deltaValue * 100) / 100);
                    $deltaY = $dy * (Utils::randByLimit($deltaValue * 100) / 100);

                    $newDx = Utils::ifIsNan($dx + $deltaX * $signX, $dx);
                    $newDy = Utils::ifIsNan($dy + $deltaY * $signY, $dy);

                    $slice = function ($val, $limit = 3) {
                        if (abs($val) > $limit) {
                            $val = 3 * (abs($val)/$val);
                        }
                        return $val;
                    };

                    if ($dx !== $newDx || $dy !== $newDy) {
                        $newDx = $slice($newDx);
                        $newDy = $slice($newDy);

                        $operation->setParams([
                            'dx' => $newDx,
                            'dy' => $newDy
                        ]);
                    }
                    break;
            }
        }
    }
}

MutationMachine::$mutationStateCalcers = [
    GeneticParam::GPT_OPSAREA => function($gen, $program, $mutationState) {
        /** @var GeneticParam $gen */
        /** @var MutationState $mutationState */
        /** @var Program $program */
        $value = $gen->getValue();
        //$qOps = $program->getOpsCnt();
        $mutationState->opArea = $gen->slice($mutationState->opArea + $value);
    },
    GeneticParam::GPT_OPSVAR => function($gen, $program, $mutationState) {
        /** @var MutationState $mutationState */
        /** @var GeneticParam $gen */
        /** @var Program $program */
        $value = $gen->getValue();
        $mutationState->opVar = $gen->slice(max($mutationState->opVar, $value));
    },
    GeneticParam::GPT_OPSCNT => function($gen, $program, $mutationState) {
        /** @var MutationState $mutationState */
        /** @var GeneticParam $gen */
        /** @var Program $program */
        $value = $gen->getValue();
        $mutationState->opCntDelta = $gen->slice($mutationState->opCntDelta + $value);
    },
    GeneticParam::GPT_GPAREA => function($gen, $program, $mutationState) {
        /** @var MutationState $mutationState */
        /** @var GeneticParam $gen */
        /** @var Program $program */
        $value = $gen->getValue();
        $mutationState->gpArea = $gen->slice($mutationState->gpArea + $value);
    },
    GeneticParam::GPT_GPVAR => function($gen, $program, $mutationState) {
        /** @var MutationState $mutationState */
        /** @var GeneticParam $gen */
        /** @var Program $program */
        $value = $gen->getValue();
        $mutationState->gpVar = $gen->slice(max($mutationState->gpVar, $value));
    },
    GeneticParam::GPT_GPCNT => function($gen, $program, $mutationState) {
        /** @var MutationState $mutationState */
        /** @var GeneticParam $gen */
        /** @var Program $program */
        $value = $gen->getValue();
        $mutationState->gpCntDelta = $gen->slice($mutationState->gpCntDelta + $value);
    },
];