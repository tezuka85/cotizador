<?php
namespace App\ClaroEnvios\Mensajerias\CostosMensajerias;



class CostosSegurosRedpack
{
    const TARIFA_ECOEXPRESS = 124.78;
    const KILOGRAMO_ECOEXPRESS = 6.05;
    const TARIFA_EXPRESS = 150.20;
    const KILOGRAMO_EXPRESS = 7.29;
    const COMBUSTIBLE = 1.135;

    public function __construct(){}

    public function getCostoZonaExtendida($peso,$tipoTarifa){
        $costoTotal = 0;
        if($tipoTarifa == 'ECOEXPRESS'){
            $costoTotal = ((self::TARIFA_ECOEXPRESS + (self::KILOGRAMO_ECOEXPRESS * ($this->calculaPesoExtra($peso))))* self::COMBUSTIBLE) * 1.16;

//            die(print_r($costoTotal));
        }elseif ($tipoTarifa == 'EXPRESS'){
            $costoTotal = ((self::TARIFA_EXPRESS + (self::KILOGRAMO_EXPRESS * ($peso - 1)))* self::COMBUSTIBLE) * 1.16;

        }

        return round($costoTotal, 2);
    }

    private function calculaPesoExtra($peso){
        $pesoExtra = 0;
        if($peso >  5){
            $pesoExtra  = $peso - 5;
        }

        return $pesoExtra;

    }

    public function getCosto($tipoTarifa){
        if($tipoTarifa == 'ECOEXPRESS'){
            $costoTotal = self::TARIFA_ECOEXPRESS;
        }elseif ($tipoTarifa == 'EXPRESS'){
            $costoTotal = self::TARIFA_EXPRESS;
        }

        return $costoTotal;
    }


}