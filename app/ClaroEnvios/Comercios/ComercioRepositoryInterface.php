<?php

namespace App\ClaroEnvios\Comercios;


interface ComercioRepositoryInterface
{

    public function guardarComercio(ComercioTO $comercioTO);

    public function registrarComercio(ComercioTO $comercioTO);

    public function actualizaComercio(ComercioTO $comercioTO);
}