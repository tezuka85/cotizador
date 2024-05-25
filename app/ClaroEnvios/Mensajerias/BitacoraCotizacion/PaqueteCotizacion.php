<?php

namespace App\ClaroEnvios\Mensajerias\BitacoraCotizacion;



use Illuminate\Database\Eloquent\Model;

class PaqueteCotizacion extends Model
{
    protected $fillable = ['id_bitacora_cotizacion','peso','largo','ancho','alto','precio'];
    protected $table = 'paquetes_cotizaciones';
    protected  $primaryKey = 'id';
    protected $dates = ['fecha_alta','fecha_modificacion'];

}
