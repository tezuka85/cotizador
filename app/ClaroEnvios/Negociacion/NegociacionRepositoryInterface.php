<?php

namespace App\ClaroEnvios\Negociacion;


interface NegociacionRepositoryInterface
{

    public function findNegociacion(NegociacionTO $negociacionTO);
}
