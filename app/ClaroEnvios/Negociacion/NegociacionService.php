<?php

namespace App\ClaroEnvios\Negociacion;


class NegociacionService implements NegociacionServiceInterface
{
    /**
     * @var NegociacionRepositoryInterface
     */
    private $negociacionRepository;

    /**
     * NegociacionService constructor.
     */
    public function __construct(
        NegociacionRepositoryInterface $negociacionRepository
    ) {
        $this->negociacionRepository = $negociacionRepository;
    }

    public function findNegociacion(NegociacionTO $negociacionTO)
    {
        return $this->negociacionRepository->findNegociacion($negociacionTO);
    }
}
