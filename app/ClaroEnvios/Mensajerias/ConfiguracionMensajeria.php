<?php

namespace App\ClaroEnvios\Mensajerias;


use Illuminate\Database\Eloquent\Model;

class ConfiguracionMensajeria extends Model
{
    protected $table = 'configuracion_mensajerias';

    public function scopeCouriers($query, $userId){
        return CostoMensajeria::join('costos_mensajerias_porcentajes', 'costos_mensajerias.id','=',
            'costos_mensajerias_porcentajes.costo_mensajeria_id')
            ->where('costos_mensajerias.usuario_id', $userId)
            ->get();
    }
}
