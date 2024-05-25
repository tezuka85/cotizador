<?php

namespace App\ClaroEnvios\Negociacion;


use Illuminate\Database\Eloquent\Model;

class Negociacion extends Model
{
    protected $table = 'negociaciones';

    public static $PORCENTAJE_POR_MENSAJERIA = 0;

    protected $hidden = [
        'pivot',
        'usuario_id',
        'updated_usuario_id',
        'created_at',
        'updated_at'
    ];
}
