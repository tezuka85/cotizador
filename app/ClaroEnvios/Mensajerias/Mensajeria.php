<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\TabuladoresMensajerias\Tabulador;
use Illuminate\Database\Eloquent\Model;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class Mensajeria extends Model
{
    use SoftDeletesBoolean;
        protected $table = 'mensajerias';

    protected $hidden = [
        'clase',
        'usuario_id',
        'logo',
        'updated_usuario_id',
        'created_at',
        'updated_at',
        'is_deleted'
    ];

    public function costosMensajeriasPorcentajes()
    {
        return $this->hasMany(CostoMensajeriaPorcentaje::class);
    }

    public function guiasSent()
    {
        return $this->hasMany('App\Comment');
    }

    public function servicios()
    {
        return $this->hasMany(ServicioMensajeria::class);
    }

    public function tabuladores()
    {
        return $this->hasMany(Tabulador::class);
    }
}
