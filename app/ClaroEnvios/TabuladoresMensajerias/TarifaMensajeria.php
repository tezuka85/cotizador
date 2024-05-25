<?php

namespace App\ClaroEnvios\TabuladoresMensajerias;


use Illuminate\Database\Eloquent\Model;

class TarifaMensajeria extends Model
{
    protected $table = 'tarifas_mensajerias';
    protected $primaryKey = 'id';
    protected $fillable = ['id_paquete','id_mensajeria','id_servicio_mensajeria','precio','peso','created_at','updated_at'];
}