<?php

namespace App\ClaroEnvios\Mensajerias\Costo;


use App\ClaroEnvios\Mensajerias\CostoMensajeriaTO;

class CostoMensajeriaValidator implements CostoMensajeriaServiceInterface
{
    /**
     * @var CostoMensajeriaService
     */
    private $costoMensajeriaService;

    /**
     * CostoMensajeriaValidator constructor.
     */
    public function __construct(CostoMensajeriaService $costoMensajeriaService)
    {
        $this->costoMensajeriaService = $costoMensajeriaService;
    }

    public function registrarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO)
    {
        $this->costoMensajeriaService->registrarCostoMensajeria($costoMensajeriaTO);
    }

    public function editarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO)
    {
        return $this->costoMensajeriaService->editarCostoMensajeria($costoMensajeriaTO);
    }

    public function obtenerCostoMensajeria($idComercio)
    {
        return $this->costoMensajeriaService->obtenerCostoMensajeria($idComercio);
    }
}
