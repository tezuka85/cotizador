<?php

namespace App\ClaroEnvios\Comandos;


interface ComandoServiceInterface
{

    public function buscarComandos(ComandoTO $comandoTO);

    public function parametrosComandoEjecucion(ComandoEjecucionTO $comandoEjecucionTO);
}
