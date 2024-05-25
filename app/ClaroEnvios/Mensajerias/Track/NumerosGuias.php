<?php

namespace App\ClaroEnvios\Mensajerias\Track;

use Jenssegers\Mongodb\Eloquent\Model;

class NumerosGuias extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'numeros_guias';

    protected $fillable = ['_id','identificador', 'mensajeria_id','guia', 'orden_id'];
    protected $dates = ['updated_at','created_at'];
}
