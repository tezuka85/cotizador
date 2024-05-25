<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoMultipleMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoMultipleMensajeriaTO;
use App\Exceptions\ValidacionException;

/**
 * Trait AccesoConfiguracionMensajeria
 * @package App\ClaroEnvios\Mensajerias
 */
trait AccesoConfiguracionMensajeria
{
    /**
     * @var
     */
    protected $configuracion;

    /**
     * @param AccesoComercioMensajeriaTO $accesoComercioMensajeriaTO
     * @throws ValidacionException
     */
    private function configurarAccesos(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTO)
    {
        if (is_null($accesoComercioMensajeriaTO->getComercioId())
                || is_null($accesoComercioMensajeriaTO->getMensajeriaId())) {
            throw new ValidacionException('Verificar que comercio_id y mensajeria_id no son nulos 1');
        }
        $accesosComercioMensajeria
            = AccesoComercioMensajeria::buscarAccesosComerciosMensajerias($accesoComercioMensajeriaTO);
        if ($accesosComercioMensajeria->count()) {
            $this->configuracion = $accesosComercioMensajeria;
        }
    }

    /**
     * Consulta las llaves configuradas con multiples usuarios para un comercio
     * @param  AccesoMultipleMensajeriaTO  $accesoMultipleMensajeriaTO
     * @return void
     * @throws ValidacionException
     */
    private function configurarMultiplesAccesos(AccesoMultipleMensajeriaTO $accesoMultipleMensajeriaTO)
    {
        if (is_null($accesoMultipleMensajeriaTO->getIdComercio())
            || is_null($accesoMultipleMensajeriaTO->getIdMensajeria())) {
            throw new ValidacionException('Verificar que comercio_id y mensajeria_id no son nulos 2 ');
        }
        $accesosMultipleMensajeria
            = AccesoMultipleMensajeria::buscarAccesosMultiplesComercios($accesoMultipleMensajeriaTO);
        if ($accesosMultipleMensajeria->count()) {
            $this->configuracion = $accesosMultipleMensajeria;
        }
    }
}
