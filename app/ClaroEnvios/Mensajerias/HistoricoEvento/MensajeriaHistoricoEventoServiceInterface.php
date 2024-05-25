<?php

namespace App\ClaroEnvios\Mensajerias\HistoricoEvento;


interface MensajeriaHistoricoEventoServiceInterface
{

    public function guardarHistoricoEventos(
        array $arrayGuiaMensajeriaTO,
        array $arrayEventosGuiasMensajerias,
        $comandoEjecucionTO = false
    );
}
