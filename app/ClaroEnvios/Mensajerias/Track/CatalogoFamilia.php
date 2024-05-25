<?php

namespace App\ClaroEnvios\Mensajerias\Track;

use Jenssegers\Mongodb\Eloquent\Model;

class CatalogoFamilia extends Model
{
    protected $connection = 'mongodb';
    protected $collection = 'catalogo_familia';

    protected $fillable = ['_id','id_mensajeria', 'codigo','estatus_externo', 'estatus_interno'];
}
