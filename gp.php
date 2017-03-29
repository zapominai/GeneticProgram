<?php

require_once 'autoload.php';

$canvasWidth  = 20;
$canvasHeight = 20;

$successCanvas = new Canvas($canvasWidth, $canvasHeight, [
    [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0],
    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0],
    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0],
    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0],
    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0],
    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0],
    [0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0],
    [0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0, 0, 0, 1, 1, 1, 0, 0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
    [0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
]);

$action = $argv[1];
$param1 = $argv[2];
$param2 = $argv[3];

if (!$action) {
    echo 'Argument`s need: 
        gp.php new
        gp.php next-gen <quantity of steps>
    ';
    die();
}

$gensParam = [
    GeneticParam::GPT_OPSAREA => new GeneticParamSettings(['default' => ['value' => 1], 'randomDelta' => [ 1 , 2 ], 'range' => [  1, 2 ], 'inPercent' => false]),
    GeneticParam::GPT_OPSVAR  => new GeneticParamSettings(['default' => ['value' => 1], 'randomDelta' => [ 1 , 2 ], 'range' => [  1, 2 ], 'inPercent' => true ]),
    GeneticParam::GPT_OPSCNT  => new GeneticParamSettings(['default' => ['value' => 1], 'randomDelta' => [-2 , 2 ], 'range' => [ -5, 5 ], 'inPercent' => false]),
    GeneticParam::GPT_GPAREA  => new GeneticParamSettings(['default' => ['value' => 1], 'randomDelta' => [ 0 , 2 ], 'range' => [ -2, 2 ], 'inPercent' => false]),
    GeneticParam::GPT_GPVAR   => new GeneticParamSettings(['default' => ['value' => 1], 'randomDelta' => [-10, 10], 'range' => [-10, 10], 'inPercent' => true ]),
    GeneticParam::GPT_GPCNT   => new GeneticParamSettings(['default' => ['value' => 0], 'randomDelta' => [-1 , 1 ], 'range' => [ -3, 3 ], 'inPercent' => false])
];

$bestFromBest = function($programFactory) {
    /** @var ProgramFactory $programFactory */
    $programFactory->killWeak();
    $newGeneration = $programFactory->getAvgChild();
    return $newGeneration;
};

$tourney = function($programFactory) {
    /** @var ProgramFactory $programFactory */
    $programFactory->sort();
    $programs = $programFactory->getPrograms();
    $populationCnt = count($programs);
    $partCnt = $programFactory->getStrategy()->getTournamentPartsSize();
    $partSize = ceil($populationCnt / $partCnt);
    $winners = [];

    $getWinners = function ($programs, $offset, $count) {
        $winners = [];
        $party = array_slice($programs, $offset, $count);
        $cntParty = count($party);

        shuffle($party);

        for ($i = 0; $i < $cntParty; $i += 2) {
            $secondKey = ($i + 1) > $cntParty - 1 ? null : $i + 1;

            /** @var Program $first */
            $first = $party[$i];

            if (is_null($secondKey)) {
                $winners[] = $first;
                return $winners;
            }

            /** @var Program $second */
            $second = $party[$secondKey];
            $winners[] = $first->getSuccess() > $second->getSuccess() ? $first : $second;
        }

        return $winners;
    };

    for ($i = 0; $i < $partCnt; $i++) {
        $winners = array_merge($winners, $getWinners($programs, $i * $partSize, $partSize));
    }

    $programFactory->setPrograms($winners);

    $newGeneration = $programFactory->getAvgChild();

    return $newGeneration;
};

$strategy1 = new GeneticStrategy([
    "successCanvas"       => $successCanvas,
    "programCount"        => 400,
    "operationCount"      => 70,
    "operationCountRange" => [70, 2500],
    "successPercent"      => 24,
    "luckyPercent"        => 0,
    "genCountRange"       => [6, 20],
    "gensParams"          => $gensParam,
    "tournamentPartsSize" => 1,
    //"doNextGen"           => $bestFromBest,
    "doNextGen"           => $tourney,
]);

$pFactory = new ProgramFactory($strategy1);

/*$pFactory->load(153);
$p1 = $pFactory->getPrograms(60817);
$pFactory->load(154);
$p2 = $pFactory->getPrograms(61207);

//$pFactory->setPrograms([$p1, $p2]);
//$pFactory->saveStateAsPNG('compare.png');

$g1 = $p1->getGensValues();
$g2 = $p2->getGensValues();

$i1 = $p1->getOpsValues();
$i2 = $p2->getOpsValues();
die();*/

switch ($action) {
    case 'new':
        db::query('TRUNCATE genetic_draw.programs');

        $pFactory->generateRandom();
        $pFactory->run();
        $pFactory->sort();
        $pFactory->save(1);
        $pFactory->saveStateAsPNG('0001.png', true);
        $pFactory->showsSuccess(1);
        break;
    case 'next-gen':
        $pFactory->load();
        $pFactory->nextGeneration($param1);
        break;
}
