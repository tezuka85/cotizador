<?php

namespace App\ClaroEnvios\Comandos;


interface ComandoRepositoryInterface
{

    public function buscarComandos(ComandoTO $comandoTO);

    public function guardarComandoEjecucion(ComandoEjecucionTO $comandoEjecucionTO);
}
