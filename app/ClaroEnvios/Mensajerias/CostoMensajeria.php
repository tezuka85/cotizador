<?php

namespace App\ClaroEnvios\Mensajerias;


use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class CostoMensajeria extends Model
{
    use SoftDeletesBoolean;
    
    protected $table = 'costos_mensajerias';
    protected $fillable = ['porcentaje','costo','porcentaje_seguro','costo_adicional','limite_costo_envio','costo_seguro','costo_zona_extendida'];
    protected $primaryKey = 'id';
    protected $dates = ['created_at','updated_at'];
    
    protected $hidden = [
        'created_at',
        'updated_at'
    ];

    public function scopeCouriers($query, $userId){
        return CostoMensajeria::join('costos_mensajerias_porcentajes', 'costos_mensajerias.id','=',
            'costos_mensajerias_porcentajes.costo_mensajeria_id')
            ->where('costos_mensajerias.usuario_id', $userId)
            ->get();
    }

}
