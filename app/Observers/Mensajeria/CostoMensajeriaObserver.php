<?php

namespace App\Observers\Mensajeria;

use App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaAuditoriaTO;
use App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaAuditoriaServiceInterface;
use App\ClaroEnvios\Mensajerias\CostoMensajeria;
use Illuminate\Support\Facades\Log;

/**
 * Class CostoMensajeriaObserver
 * @package App\Observers\Mensajeria
 */
class CostoMensajeriaObserver
{
    /**
     * @var CostoMensajeriaAuditoriaServiceInterface
     */
    private $costoMensajeriaAuditoriaService;

    /**
     * CostoMensajeriaObserver constructor.
     */
    public function __construct(
        CostoMensajeriaAuditoriaServiceInterface $costoMensajeriaAuditoriaService
    ) {
        $this->costoMensajeriaAuditoriaService = $costoMensajeriaAuditoriaService;
    }

    /**
     * @param CostoMensajeria $costoMensajeria
     */
    public function created(CostoMensajeria $costoMensajeria)
    {
        $costoMensajeriaAuditoriaTO = new CostoMensajeriaAuditoriaTO();
        $costoMensajeriaAuditoriaTO->setDatosCostoMensajeria($costoMensajeria);
        $costoMensajeriaAuditoriaTO->setUsuarioId(auth()->id());
        $costoMensajeriaAuditoriaTO->setFuncion(__FUNCTION__);
        $this->costoMensajeriaAuditoriaService->guardarCostoMensajeria($costoMensajeriaAuditoriaTO);
    }

    /**
     * @param CostoMensajeria $costoMensajeria
     */
    public function saved(CostoMensajeria $costoMensajeria)
    {
        $costoMensajeriaAuditoriaTO = new CostoMensajeriaAuditoriaTO();
        $costoMensajeriaAuditoriaTO->setDatosCostoMensajeria($costoMensajeria);
        $costoMensajeriaAuditoriaTO->setFuncion(__FUNCTION__);
        $costoMensajeriaAuditoriaTO->setUsuarioId(auth()->id());

        if (!$costoMensajeria->wasRecentlyCreated) {
            $this->costoMensajeriaAuditoriaService->guardarCostoMensajeria($costoMensajeriaAuditoriaTO);
        }
    }

    /**
     * @param CostoMensajeria $costoMensajeria
     */
    public function deleted(CostoMensajeria $costoMensajeria)
    {
        $costoMensajeriaAuditoriaTO = new CostoMensajeriaAuditoriaTO();
        $costoMensajeriaAuditoriaTO->setDatosCostoMensajeria($costoMensajeria);
        $costoMensajeriaAuditoriaTO->setFuncion(__FUNCTION__);
        $costoMensajeriaAuditoriaTO->setUsuarioId(auth()->id());
        $costoMensajeriaAuditoriaTO->setUpdatedUsuarioId(auth()->id());
        $this->costoMensajeriaAuditoriaService->guardarCostoMensajeria($costoMensajeriaAuditoriaTO);
    }
}
