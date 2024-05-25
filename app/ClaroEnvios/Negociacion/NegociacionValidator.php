<?php

namespace App\ClaroEnvios\Negociacion;


class NegociacionValidator implements NegociacionServiceInterface
{
    /**
     * @var NegociacionService
     */
    private $negociacionService;


    /**
     * NegociacionValidator constructor.
     */
    public function __construct(NegociacionService $negociacionService)
    {
        $this->negociacionService = $negociacionService;
    }

    public function findNegociacion(NegociacionTO $negociacionTO)
    {
        return $this->negociacionService->findNegociacion($negociacionTO);
    }
}
