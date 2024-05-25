<?php

namespace App\ClaroEnvios\Negociacion;


class NegociacionRepository implements NegociacionRepositoryInterface
{

    public function findNegociacion(NegociacionTO $negociacionTO)
    {
        return Negociacion::find($negociacionTO->getId());
    }
}
