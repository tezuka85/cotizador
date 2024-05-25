<?php

namespace App\ClaroEnvios\Mensajerias\Costo;

use App\ClaroEnvios\Mensajerias\CostoMensajeriaTO;

class CostoMensajeriaService implements CostoMensajeriaServiceInterface
{
    /**
     * @var CostoMensajeriaRepositoryInterface
     */
    private $costoMensajeriaRepository;

    /**
     * CostoMensajeriaService constructor.
     */
    public function __construct(
        CostoMensajeriaRepositoryInterface $costoMensajeriaRepository
    ) {
        $this->costoMensajeriaRepository = $costoMensajeriaRepository;
    }

    public function registrarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO)
    {
        $this->costoMensajeriaRepository->registrarCostoMensajeria($costoMensajeriaTO);
    }

    public function editarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO)
    {
        return $this->costoMensajeriaRepository->editarCostoMensajeria($costoMensajeriaTO);
    }

    public function obtenerCostoMensajeria($idComercio)
    {
        return $this->costoMensajeriaRepository->obtenerCostoMensajeria($idComercio);
    }
}
