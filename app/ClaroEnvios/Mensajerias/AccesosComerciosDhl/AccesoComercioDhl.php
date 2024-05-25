<?php

namespace App\ClaroEnvios\Mensajerias\AccesosComerciosDhl;


use Illuminate\Database\Eloquent\Model;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class AccesoComercioDhl extends Model
{
     use SoftDeletesBoolean;
     const IS_DELETED = 'estatus';
    protected $table = 'accesos_comercios_dhl';

    protected $fillable = ['id_comercio','estatus','created_at','updated_at'];

    
}
