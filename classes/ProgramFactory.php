<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 18.03
 * Time: 18:36
 */
class ProgramFactory
{
    /** @var Program[] $_programs*/
    private $_programs;
    private $_curGeneration;
    private $_success;
    private $_debug = false;
    /** @var GeneticStrategy $_strategy*/
    private $_strategy;

    /**
     * ProgramFactory constructor.
     * @param GeneticStrategy $strategy
     * @internal param $_programs
     */
    public function __construct($strategy)
    {
        $this->_strategy = $strategy;
        $this->_programs = [];
        $this->_curGeneration = 0;
    }

    public function generateRandom($quantityProgram = 0, $quantityOps = 0)
    {
        $strategy = $this->_strategy;

        if (empty($quantityProgram)) $quantityProgram = $strategy->getProgramCount();
        if (empty($quantityOps)) $quantityOps = $strategy->getOperationCount();

        $opsFactory = new OperationFactory();
        $gensFactory = new GeneticParamFactory($strategy->getGensParams());

        foreach (range(0, $quantityProgram - 1) as $i) {
            $this->_programs[] = new Program(
                $opsFactory->generateRandom($quantityOps),
                $gensFactory->generateRandom()
            );
        }
    }

    public function run() {
        $min = 100;
        $max = 0;
        $sumSuccess = 0;
        $successCanvas = $this->_strategy->getSuccessCanvas();

        /** @var Program $program */
        foreach ($this->_programs as $program) {
            $program->run(new Artist(new Canvas($successCanvas->getWidth(), $successCanvas->getHeight())), $successCanvas);

            if ($program->getSuccess() < $min) $min = $program->getSuccess();
            if ($program->getSuccess() > $max) $max = $program->getSuccess();

            $sumSuccess += $program->getSuccess();
        }

        $avg = $sumSuccess / count($this->_programs);

        $this->_success['min'] = $min;
        $this->_success['max'] = $max;
        $this->_success['avg'] = $avg;
    }

    public function save($generation) {
        /** @var Program $program */
        foreach ($this->_programs as $program) {
            $program->save($generation);
        }
    }

    public function load($generation = null, $id = null)
    {
        if (is_null($generation)) {
            $generation = db::queryVal('select max(generation) from `genetic_draw`.`programs`');
        }

        $programs = db::query('        
            select
                `id`,
                `parent_id`,
                `generation`,
                `body`,
                `success`,
                `size`,
                `result`,
                `gens`
            from `genetic_draw`.`programs` p
            where 1 
                and p.generation = ?
            order by p.success desc
        ', $generation);

        $this->_programs = [];

        /** @var Program $program */
        foreach ($programs as $program) {
            if (!is_null($id) && intval($program->id) !== $id) {
                continue;
            }

            $this->_programs[] = (new Program())->load($program);
        }

        $this->_curGeneration = $generation;
    }

    /**
     * @param $fileName
     * @param Canvas|null $showSuccessCanvas
     */
    public function saveStateAsPNG($fileName, $showSuccessCanvas = false)
    {
        $borderSize = 15;
        $paddingBottom = 24;
        $paddingTopLabel = 5;
        $maxWidth = 900;

        $progCnt = count($this->_programs);
        if ($showSuccessCanvas) $progCnt++;

        $canvasWidth  = $this->_programs[0]->getResult()->getWidth();
        $canvasHeight = $this->_programs[0]->getResult()->getHeight();

        $cntInLine = ceil($maxWidth / ($canvasWidth + $borderSize * 2));

        $imageWidth = ($canvasWidth + $borderSize * 2) * $cntInLine;
        $imageHeight = ($canvasHeight + $borderSize + $paddingBottom) * ceil($progCnt / $cntInLine) + $paddingBottom;

        $image = @imagecreate($imageWidth, $imageHeight) or die("Cannot Initialize new GD image stream");

        $bgColor = imagecolorallocate($image, 0xFF, 0xFF, 0xFF);
        $pcBlack = imagecolorallocate($image, 0, 0, 0);
        $tColor = imagecolorallocate($image, 133, 14, 91);

        $startX = 0;
        $startY = $borderSize;
        $canvasCnt = 0;

        foreach ($this->_programs as $pIndex => $program) {
            /** @var Canvas $canvas */
            $canvas = $program->getResult();

            $startX += $borderSize;

            /** @var Program $program */
            $label = round($program->getSuccess() * 100, 2) . '%' . PHP_EOL .
                $program->getGensInfo() . PHP_EOL .
                ($program->getIsLucky() ? '*' : '') . count($program->getOperations());

            $canvas->save2image($image, $startX, $startY, $paddingTopLabel, $label, $pcBlack, $tColor);

            $startX += ($canvasWidth + $borderSize);
            $canvasCnt++;

            if ($canvasCnt >= $cntInLine) {
                $startY += ($canvasHeight + $borderSize + $paddingBottom);
                $startX = 0;
                $canvasCnt = 0;
            }
        }

        if ($showSuccessCanvas) {
            $startX += $borderSize;
            $this->_strategy->getSuccessCanvas()->save2image($image, $startX, $startY, $paddingTopLabel, '100%', $pcBlack, $tColor);
        }

        imagepng($image, $fileName);
        imagedestroy($image);
    }

    public function nextGeneration($generationCnt = 1)
    {
        foreach (range(0, $generationCnt - 1) as $i) {
            $mainTimer = new Timer('main');

            $nextGenTimer = new Timer('next-gen');
            $doNexGen = $this->_strategy->doNextGen;
            $newGeneration = $doNexGen($this);
            $nextGenTimer->stop();

            $this->_programs = $newGeneration;
            $this->_curGeneration++;

            $runTimer = new Timer('run');
            $this->run();
            $runTimer->stop();

            //$saveTimer = new Timer('save2db');
            $this->save($this->_curGeneration);
            //$saveTimer->stop();

            $this->sort();

            //$saveImageTimer = new Timer('save2png');
            $this->saveStateAsPNG(str_pad($this->_curGeneration, 4, '0', STR_PAD_LEFT) . '.png', true);
            //$saveImageTimer->stop();

            $mainTimer->stop();
            $this->showsSuccess(null, [$mainTimer, $nextGenTimer, $runTimer]);
        }
    }

    public function sort($comparator = null) {
        if (is_null($comparator)) {
            $comparator = function ($a, $b) {
                /** @var Program $a */
                /** @var Program $b */
                if ($a->getSuccess() === $b->getSuccess()) {
                    return 0;
                }
                return $a->getSuccess() < $b->getSuccess() ? 1 : -1;
            };
        }

        uasort($this->_programs, $comparator);
        return $this->_programs;
    }

    public function killWeak($successPercent = null)
    {
        if (is_null($successPercent)) {
            $successPercent = $this->_strategy->getSuccessPercent();
        }

        $this->sort();

        $pCnt = count($this->_programs);
        $strongCnt = round(($pCnt * $successPercent) / 100);

        $luckyPercent = $this->_strategy->getLuckyPercent();
        $luckyCnt = floor($pCnt * $luckyPercent / 100);

        if ($luckyCnt > 0) {
            // Есть счастливчики
            foreach (range(0, $luckyCnt - 1) as $i) {
                $unluckyIndex = rand(0, $strongCnt - 1);
                $luckyIndex = rand($strongCnt, $pCnt - 1);

                $tmp = $this->_programs[$unluckyIndex];
                $this->_programs[$unluckyIndex] = $this->_programs[$luckyIndex];
                $this->_programs[$luckyIndex] = $tmp;

                $this->_programs[$unluckyIndex]->setIsLucky(true);
            }
        }

        $this->_programs = array_slice($this->_programs, 0, $strongCnt);
        return $this->_programs;
    }

    public function showsSuccess($generation = null, $timers = [])
    {
        if (is_null($generation)) {
            $generation = $this->_curGeneration;
        }

        $timeStr = "";
        /** @var Timer $timer */
        foreach ($timers as $timer) {
            $timeStr .= $timer->getName() . " " . sprintf('%.2F', $timer->getTime() / 60) . "; ";
        }

        echo sprintf('G #%03d (min: %.4F; max: %.4F; avg: %.4F) %s' . PHP_EOL,
            $generation, $this->_success['min'], $this->_success['max'], $this->_success['avg'], $timeStr
        );
    }

    /**
     * @param int $id
     * @return Program[]|Program
     */
    public function getPrograms($id = 0)
    {
        if (empty($id)) return $this->_programs;

        return array_pop(array_filter($this->_programs, function ($item) use ($id){
            /** @var Program $item */
            return $item->getId() == $id;
        }));
    }

    /**
     * @return boolean
     */
    public function isDebug()
    {
        return $this->_debug;
    }

    public function getAvgChild()
    {
        $newGeneration = [];
        $populationCnt = $this->_strategy->getProgramCount();
        $avgChildCnt = floor($populationCnt / count($this->_programs));
        $childBonus = $populationCnt - $avgChildCnt * count($this->_programs);

        /** @var Program $program */
        foreach ($this->getPrograms() as $pIndex => $program) {
            $childCnt = $avgChildCnt + ($childBonus > 0 ? 1 : 0);
            $childBonus--;

            if ($this->isDebug()) {
                echo sprintf('Производим потомство от программы №%d, количество: %d' . PHP_EOL, $program->getId(), $childCnt);
            }

            for ($i = 0; $i < $childCnt; $i++) {
                $newGeneration[] = $program->getChild($this->getStrategy());
            }
        }

        return $newGeneration;
    }

    /**
     * @param Program[] $programs
     */
    public function setPrograms($programs)
    {
        $this->_programs = $programs;
    }

    /**
     * @return GeneticStrategy
     */
    public function getStrategy()
    {
        return $this->_strategy;
    }
}