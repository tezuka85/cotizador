<?php

namespace App\ClaroEnvios\Mensajerias\HistoricoEvento;


class MensajeriaHistoricoEventoValidator implements MensajeriaHistoricoEventoServiceInterface
{
    /**
     * @var MensajeriaHistoricoEventoService
     */
    private $mensajeriaHistoricoEventoService;

    /**
     * MensajeriaHistoricoEventoValidator constructor.
     */
    public function __construct(
        MensajeriaHistoricoEventoService $mensajeriaHistoricoEventoService
    ) {
        $this->mensajeriaHistoricoEventoService = $mensajeriaHistoricoEventoService;
    }

    public function guardarHistoricoEventos(
        array $arrayGuiaMensajeriaTO,
        array $arrayEventosGuiasMensajerias,
        $comandoEjecucionTO = false
    ) {
        return $this->mensajeriaHistoricoEventoService
            ->guardarHistoricoEventos(
                $arrayGuiaMensajeriaTO,
                $arrayEventosGuiasMensajerias,
                $comandoEjecucionTO
            );
    }
}
