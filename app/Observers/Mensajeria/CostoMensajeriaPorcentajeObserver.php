<?php

namespace App\Observers\Mensajeria;

use App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaAuditoriaServiceInterface;
use App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaPorcentajeAuditoriaTO;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaPorcentaje;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaPorcentajeTO;
use Illuminate\Support\Facades\Log;

/**
 * Class CostoMensajeriaPorcentajeObserver
 * @package App\Observers\Mensajeria
 */
class CostoMensajeriaPorcentajeObserver
{
    /**
     * @var CostoMensajeriaAuditoriaServiceInterface
     */
    private $mensajeriaAuditoriaService;

    /**
     * CostoMensajeriaPorcentajeObserver constructor.
     */
    public function __construct(
        CostoMensajeriaAuditoriaServiceInterface $costoMensajeriaAuditoriaService
    ) {
        $this->costoMensajeriaAuditoriaService = $costoMensajeriaAuditoriaService;
    }

    /**
     * @param CostoMensajeriaPorcentaje $costoMensajeriaPorcentaje
     */
    public function created(CostoMensajeriaPorcentaje $costoMensajeriaPorcentaje)
    {
        Log::info('CMPcreated');
        $costoMensajeriaPorcentajeAuditoriaTO = new CostoMensajeriaPorcentajeAuditoriaTO();
        $costoMensajeriaPorcentajeAuditoriaTO->setDatosCostoMensajeriaPorcentaje($costoMensajeriaPorcentaje);
        $costoMensajeriaPorcentajeAuditoriaTO->setFuncion(__FUNCTION__);
        $this->costoMensajeriaAuditoriaService
            ->guardarCostoMensajeriaPorcentaje($costoMensajeriaPorcentajeAuditoriaTO);
    }

    /**
     * @param CostoMensajeriaPorcentaje $costoMensajeriaPorcentaje
     */
    public function saved(CostoMensajeriaPorcentaje $costoMensajeriaPorcentaje)
    {
        Log::info('CMPupdated');
        $costoMensajeriaPorcentajeAuditoriaTO = new CostoMensajeriaPorcentajeAuditoriaTO();
        $costoMensajeriaPorcentajeAuditoriaTO->setDatosCostoMensajeriaPorcentaje($costoMensajeriaPorcentaje);
        $costoMensajeriaPorcentajeAuditoriaTO->setFuncion(__FUNCTION__);
        if (!$costoMensajeriaPorcentaje->wasRecentlyCreated) {
            $this->costoMensajeriaAuditoriaService
                ->guardarCostoMensajeriaPorcentaje($costoMensajeriaPorcentajeAuditoriaTO);
        }
    }
    /**
     * @param CostoMensajeriaPorcentaje $costoMensajeriaPorcentaje
     */
    public function deleted(CostoMensajeriaPorcentaje $costoMensajeriaPorcentaje)
    {
        Log::info('CMPdeleted');
        $costoMensajeriaPorcentajeAuditoriaTO = new CostoMensajeriaPorcentajeAuditoriaTO();
        $costoMensajeriaPorcentajeAuditoriaTO->setDatosCostoMensajeriaPorcentaje($costoMensajeriaPorcentaje);
        $costoMensajeriaPorcentajeAuditoriaTO->setFuncion(__FUNCTION__);
        $costoMensajeriaPorcentajeAuditoriaTO->setUpdatedUsuarioId(auth()->id());
        $this->costoMensajeriaAuditoriaService
            ->guardarCostoMensajeriaPorcentaje($costoMensajeriaPorcentajeAuditoriaTO);
    }
}
