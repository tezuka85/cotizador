<?php

namespace App\ClaroEnvios\TabuladoresMensajerias;

use App\ClaroEnvios\Mensajerias\Mensajeria;
use App\ClaroEnvios\Mensajerias\ServicioMensajeria;
use Illuminate\Database\Eloquent\Model;

class Tabulador extends Model
{
    protected $table = 'tabuladores';
    protected $dates = ['created_at','updated_at'];

    protected $fillable = [
        'id',
        'mensajeria_id',
        'servicio_mensajeria_id',
        'zona_envio',
        'zona_recepcion',
        'peso',
        'precio'
    ];

    public function servicio()
    {
        return $this->hasOne(ServicioMensajeria::class,'id','servicio_mensajeria_id');
    }

    public function mensajeria()
    {
        return $this->hasOne(Mensajeria::class,'id','mensajeria_id');
    }

}
