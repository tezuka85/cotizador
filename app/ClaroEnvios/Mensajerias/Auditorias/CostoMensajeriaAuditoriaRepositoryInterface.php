<?php

namespace App\ClaroEnvios\Mensajerias\Auditorias;


interface CostoMensajeriaAuditoriaRepositoryInterface
{

    public function guardarCostoMensajeria(CostoMensajeriaAuditoriaTO $costoMensajeriaAuditoriaTO);
}