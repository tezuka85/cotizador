<?php

namespace App\ClaroEnvios\TabuladoresMensajerias;


use Illuminate\Database\Eloquent\Model;

class TarifaMensajeriaZona extends Model
{
    protected $table = 'tarifas_mensajerias_zonas';
    protected $primaryKey = 'id';
    protected $fillable = ['id','id_mensajeria','id_servicio_mensajeria','zona_estado','kg','precio','tipo_cobertura','created_at','updated_at'];
}