<?php

namespace App\ClaroEnvios\Mensajerias;


use Illuminate\Database\Eloquent\Model;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class ServicioMensajeria extends Model
{
    use SoftDeletesBoolean;
    protected $table = 'servicios_mensajerias';

    public function mensajeria()
    {
        return $this->belongsTo(Mensajeria::class);
    }
}
