<?php

namespace App\ClaroEnvios\Mensajerias\Costo;


use App\ClaroEnvios\Mensajerias\CostoMensajeriaTO;

interface CostoMensajeriaRepositoryInterface
{

    public function registrarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO);
    public function editarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO);
    public function obtenerCostoMensajeria($idComercio);

}
