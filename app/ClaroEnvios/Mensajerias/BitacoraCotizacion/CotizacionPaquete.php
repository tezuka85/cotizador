<?php

namespace App\ClaroEnvios\Mensajerias\BitacoraCotizacion;


use Illuminate\Database\Eloquent\Model;

class CotizacionPaquete extends Model
{
    const UPDATED_AT = null;

    protected $table = 'cotizaciones_paquetes';
    protected $primaryKey = 'id_cotizaciones_paquetes';
    protected $dates = ['created_at'];

    protected $fillable = ['id_bitacora_cotizacion','id_paquete','estatus','created_at'];

}
