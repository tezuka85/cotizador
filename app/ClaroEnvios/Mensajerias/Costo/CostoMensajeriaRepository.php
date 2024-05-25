<?php

namespace App\ClaroEnvios\Mensajerias\Costo;


use App\ClaroEnvios\Mensajerias\CostoMensajeria;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaPorcentaje;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaPorcentajeTO;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaTO;
use App\Exceptions\ValidacionException;
use Dotenv\Exception\ValidationException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class CostoMensajeriaRepository implements CostoMensajeriaRepositoryInterface
{

    public function registrarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO)
    {
        DB::transaction(
            function () use ($costoMensajeriaTO) {
                //die("<pre>".print_r($costoMensajeriaTO));
                $costoMensajeria = new CostoMensajeria();
                $costoMensajeria->mensajeria_id = $costoMensajeriaTO->getMensajeriaId();
                $costoMensajeria->comercio_id = $costoMensajeriaTO->getComercioId();
                $costoMensajeria->negociacion_id = $costoMensajeriaTO->getNegociacionId();
                $costoMensajeria->porcentaje = $costoMensajeriaTO->getPorcentaje();
                $costoMensajeria->costo = $costoMensajeriaTO->getCosto();
                $costoMensajeria->porcentaje_seguro = $costoMensajeriaTO->getPorcentajeSeguro();

                $costoMensajeria->save();
                $costoMensajeriaTO->setId($costoMensajeria->id);
            }
        );
    }



    public function editarCostoMensajeria(CostoMensajeriaTO $costoMensajeriaTO)
    {
        Log::info('---Entra en editarCostoMensajeria----');
        $costoMensajeria = CostoMensajeria::withTrashed()->where('comercio_id',$costoMensajeriaTO->getComercioId())
            ->where('mensajeria_id',$costoMensajeriaTO->getMensajeriaId())
            ->first();

//        die(var_dump($costoMensajeria->trashed()));
        if($costoMensajeria->trashed()){
            throw new \Exception("Negociacion desactivada {$costoMensajeriaTO->getNegociacionId()}, no es posible editarla",402);
        }

        $costoMensajeria->negociacion_id = $costoMensajeriaTO->getNegociacionId();
        $costoMensajeria->porcentaje = $costoMensajeriaTO->getPorcentaje();
        $costoMensajeria->porcentaje_seguro = $costoMensajeriaTO->getPorcentajeSeguro();
        $costoMensajeria->costo = $costoMensajeriaTO->getCosto();
        $costoMensajeria->costo_adicional = $costoMensajeriaTO->getCostoAdicional();
        $costoMensajeria->limite_costo_envio = $costoMensajeriaTO->getLimiteCostoEnvio();
        $costoMensajeria->costo_seguro= $costoMensajeriaTO->getCostoSeguro();
        $costoMensajeria->update();
        Log::info('---Guarda costos_mensajerias comercio: '.$costoMensajeriaTO->getComercioId());

        return $costoMensajeria;

    }

    public function obtenerCostoMensajeria($comercioId){

        Log::info('---Entra en obtenerCostoMensajeria----');
        $costosMensajeria = CostoMensajeria::where('comercio_id',$comercioId)->get();

        if ($costosMensajeria->isEmpty()) {
            return null;
        }

        return $costosMensajeria;
    }
    
}
