<?php

namespace App\ClaroEnvios\Comercios\Direcciones;


use Illuminate\Database\Eloquent\Model;

class ComercioDireccion extends Model
{
    protected $table = 'comercios_direcciones';
    protected $fillable = ['codigo_postal','estado','colonia','municipio','calle','numero','referencias','comercio_id','direccion_tipo_id',
        'usuario_id','updated_usuario_id'];

    public function scopeDireccionFiscal($query, $comercioId)
    {
        return $query->select('comercios_direcciones.*')
            ->join('comercios','comercios_direcciones.comercio_id' , '=', 'comercios.id')
            ->where('direccion_tipo_id', 1)
            ->where('comercios_direcciones.comercio_id',$comercioId)
            ->first();
    }
}