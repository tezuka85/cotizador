<?php

namespace App\ClaroEnvios\Mensajerias\Costo;


use App\ClaroEnvios\Mensajerias\CostoMensajeriaTO;

interface CostoMensajeriaServiceInterface
{

    public function registrarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO);
    public function editarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO);
}
