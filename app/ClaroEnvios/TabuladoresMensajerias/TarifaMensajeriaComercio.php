<?php

namespace App\ClaroEnvios\TabuladoresMensajerias;


use Illuminate\Database\Eloquent\Model;

class TarifaMensajeriaComercio extends Model
{
    protected $table = 'tarifas_mensajerias_comercios';
    protected $primaryKey = 'id';
    protected $fillable = ['id_paquete','id_mensajeria','id_servicio_mensajeria','id_mensajeria','precio','peso','created_at','updated_at'];
}