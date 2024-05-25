<?php

namespace App\ClaroEnvios\Mensajerias\Costo;

use App\ClaroEnvios\Mensajerias\ConfiguracionMensajeriaTO;

class ConfiguracionMensajeriaService
{
    /**
     * @var CostoMensajeriaRepositoryInterface
     */
    private $configuracionMensajeriaRepository;

    /**
     * CostoMensajeriaService constructor.
     */
    public function __construct(
        ConfiguracionMensajeriaRepository $configuracionMensajeriaRepository
    ) {
        $this->configuracionMensajeriaRepository = $configuracionMensajeriaRepository;
    }

    public function registrarConfiguracionMensajeria(ConfiguracionMensajeriaTO $configuracionMensajeriaTO)
    {
        $this->configuracionMensajeriaRepository->registrarConfiguracionMensajeria($configuracionMensajeriaTO);
    }
}
