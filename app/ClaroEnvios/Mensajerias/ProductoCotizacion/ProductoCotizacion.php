<?php

namespace App\ClaroEnvios\Mensajerias\ProductoCotizacion;


use Illuminate\Database\Eloquent\Model;

class ProductoCotizacion extends Model
{
    protected $fillable = ['id_bitacora_cotizacion','descripcion_sat','codigo_sat','peso','largo','ancho','alto','precio'];
    protected $table = 'productos_cotizaciones';
    protected  $primaryKey = 'id';
    protected $dates = ['fecha_alta','fecha_modificacion'];


}
