<?php

namespace App\ClaroEnvios\Mensajerias\Auditorias;


class CostoMensajeriaAuditoriaService implements CostoMensajeriaAuditoriaServiceInterface
{
    /**
     * @var CostoMensajeriaAuditoriaRepositoryInterface
     */
    private $costoMensajeriaAuditoriaRepository;

    /**
     * CostoMensajeriaAuditoriaService constructor.
     */
    public function __construct(
        CostoMensajeriaAuditoriaRepositoryInterface $costoMensajeriaAuditoriaRepository
    ) {
        $this->costoMensajeriaAuditoriaRepository = $costoMensajeriaAuditoriaRepository;
    }

    public function guardarCostoMensajeria(CostoMensajeriaAuditoriaTO $costoMensajeriaAuditoriaTO)
    {
        $this->costoMensajeriaAuditoriaRepository->guardarCostoMensajeria($costoMensajeriaAuditoriaTO);
    }

}