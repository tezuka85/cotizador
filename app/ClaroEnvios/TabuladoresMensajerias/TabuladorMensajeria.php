<?php

namespace App\ClaroEnvios\TabuladoresMensajerias;

use Illuminate\Database\Eloquent\Model;

class TabuladorMensajeria extends Model
{
    protected $table = 'tabuladores_mensajerias';
    protected $dates = ['created_at','updated_at'];

    protected $fillable = [
        'id',
        'mensajeria_id',
        'tipo_servicio',
        'zona_envio',
        'zona_recepcion',
        'kg',
        'precio',
        'usuario_id',
        'created_at',
        'updated_at'
    ];

}
