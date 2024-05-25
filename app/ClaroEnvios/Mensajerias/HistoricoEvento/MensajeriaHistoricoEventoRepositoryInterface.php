<?php

namespace App\ClaroEnvios\Mensajerias\HistoricoEvento;


interface MensajeriaHistoricoEventoRepositoryInterface
{

    public function guardarHistoricoEventos(
        array $arrayGuiaMensajeriaTO,
        array $arrayEventosGuiasMensajerias,
        $comandoEjecucionTO = false
    );
}
