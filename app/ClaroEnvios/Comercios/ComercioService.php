<?php

namespace App\ClaroEnvios\Comercios;


class ComercioService implements ComercioServiceInterface
{
    /**
     * @var ComercioRepositoryInterface
     */
    private $comercioRepository;


    /**
     * ComercioService constructor.
     */
    public function __construct(ComercioRepositoryInterface $comercioRepository)
    {
        $this->comercioRepository = $comercioRepository;
    }

    public function registrarComercio(ComercioTO $comercioTO)
    {
        $this->comercioRepository->registrarComercio($comercioTO);
    }
}