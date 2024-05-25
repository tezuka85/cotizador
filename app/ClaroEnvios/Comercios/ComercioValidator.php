<?php

namespace App\ClaroEnvios\Comercios;


class ComercioValidator implements ComercioServiceInterface
{
    /**
     * @var ComercioService
     */
    private $comercioService;

    /**
     * ComercioValidator constructor.
     */
    public function __construct(ComercioService $comercioService)
    {
        $this->comercioService = $comercioService;
    }

    public function registrarComercio(ComercioTO $comercioTO)
    {
        $this->comercioService->registrarComercio($comercioTO);
    }
}