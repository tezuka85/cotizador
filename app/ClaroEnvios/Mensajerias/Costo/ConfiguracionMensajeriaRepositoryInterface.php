<?php

namespace App\ClaroEnvios\Mensajerias\Costo;


use App\ClaroEnvios\Mensajerias\ConfiguracionMensajeriaTO;

interface ConfiguracionMensajeriaRepositoryInterface
{

    public function registrarConfiguracionMensajeria(ConfiguracionMensajeriaTO $configuracionMensajeriaTO);
}
