<?php

namespace App\ClaroEnvios\Mensajerias\Costo;


use App\ClaroEnvios\Mensajerias\ConfiguracionMensajeria;
use App\ClaroEnvios\Mensajerias\ConfiguracionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\CostoMensajeria;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaPorcentaje;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaPorcentajeTO;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaTO;
use App\ClaroEnvios\Mensajerias\MensajeriaPorcentajeCosto;
use App\ClaroEnvios\Mensajerias\MensajeriaPorcentajeCostoTO;
use Illuminate\Support\Facades\DB;

class ConfiguracionMensajeriaRepository
{

    public function registrarConfiguracionMensajeria(ConfiguracionMensajeriaTO $configuracionMensajeriaTO,
    MensajeriaPorcentajeCosto $mensajeriaPorcentajeCosto)
    {
        DB::transaction(
            function () use ($configuracionMensajeriaTO, $mensajeriaPorcentajeCosto) {
                $this->guardarConfiguracionMensajeria($configuracionMensajeriaTO, $mensajeriaPorcentajeCosto);
            }
        );
    }

    private function guardarConfiguracionMensajeria(ConfiguracionMensajeriaTO $configuracionMensajeriaTO,
                                                    MensajeriaPorcentajeCostoTO $mensajeriaPorcentajeCostoTO)
    {
        $configuracionMensajeria = new ConfiguracionMensajeria();
        $configuracionMensajeria->mensajeria_id = $configuracionMensajeriaTO->getMensajeriaId();
        $configuracionMensajeria->comercio_id = $configuracionMensajeriaTO->getComercioId();
        $configuracionMensajeria->negociacion_id = $configuracionMensajeriaTO->getNegociacionId();
        $configuracionMensajeria->tipo_configuracion = $configuracionMensajeriaTO->getTipoConfiguracion();
        $configuracionMensajeria->tipo_calculo = $configuracionMensajeriaTO->getTipoCalculo();
        $configuracionMensajeria->porcentaje_seguro = $configuracionMensajeriaTO->getPorcentajeSeguro();
        $configuracionMensajeria->save();
        //$configuracionMensajeriaTO->setId(1);
        $configuracionMensajeriaTO->setId($configuracionMensajeria->id);

        if($configuracionMensajeriaTO->getTipoConfiguracion() == ConfiguracionMensajeriaTO::$tipoConfiguracion['claroEnvios']
            && $configuracionMensajeriaTO->getNegociacionId() == ConfiguracionMensajeriaTO::$tipoConfiguracion['comercio']){

            $mensajeriaPorcentajeCosto = new MensajeriaPorcentajeCosto();
            $mensajeriaPorcentajeCosto->configutacion_id = $configuracionMensajeriaTO->getId();
            if($configuracionMensajeriaTO->getTipoCalculo() == ConfiguracionMensajeriaTO::$tipoCalculo['porcentaje'])
                $mensajeriaPorcentajeCosto->porcentaje = $mensajeriaPorcentajeCostoTO->getPorcentaje();
            else if($configuracionMensajeriaTO->getTipoCalculo() == ConfiguracionMensajeriaTO::$tipoCalculo['costo'])
                $mensajeriaPorcentajeCosto->costo = $mensajeriaPorcentajeCostoTO->getCosto();

                    die("<pre>".print_r('calro'));
        }
    }
}
