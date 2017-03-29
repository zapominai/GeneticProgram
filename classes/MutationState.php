<?php

/**
 * Created by PhpStorm.
 * User: heavy
 * Date: 19.03
 * Time: 14:28
 */
class MutationState
{
    public $opArea; // кол-во операций затрагиваемых мутацией, %
    public $opCntDelta; // прирост/убыль кол-ва операций
    public $opVar; // кол-во процентов операций которых затронут изменения

    public $gpArea; // кол-во генов затрагиваемых мутацией
    public $gpCntDelta; // приротс/убыль генов
    public $gpVar; // кол-во процентов на которое будут изменены параметры генов

    /**
     * MutationState constructor.
     */
    public function __construct()
    {
        $this->opArea     = 0.0;
        $this->opVar      = 0.0;
        $this->opCntDelta = 0.0;
        $this->gpArea     = 0.0;
        $this->gpCntDelta = 0.0;
        $this->gpVar      = 0.0;
    }
}