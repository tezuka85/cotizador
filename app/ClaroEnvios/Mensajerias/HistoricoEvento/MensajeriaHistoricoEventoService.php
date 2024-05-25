<?php

namespace App\ClaroEnvios\Mensajerias\HistoricoEvento;


class MensajeriaHistoricoEventoService implements MensajeriaHistoricoEventoServiceInterface
{
    /**
     * @var MensajeriaHistoricoEventoRepositoryInterface
     */
    private $mensajeriaHistoricoEventoRepository;

    /**
     * MensajeriaHistoricoEventoService constructor.
     */
    public function __construct(
        MensajeriaHistoricoEventoRepositoryInterface $mensajeriaHistoricoEventoRepository
    ) {
        $this->mensajeriaHistoricoEventoRepository = $mensajeriaHistoricoEventoRepository;
    }

    public function guardarHistoricoEventos(
        array $arrayGuiaMensajeriaTO,
        array $arrayEventosGuiasMensajerias,
        $comandoEjecucionTO = false
    ) {
        return $this->mensajeriaHistoricoEventoRepository
            ->guardarHistoricoEventos(
                $arrayGuiaMensajeriaTO,
                $arrayEventosGuiasMensajerias,
                $comandoEjecucionTO
            );
    }
}
