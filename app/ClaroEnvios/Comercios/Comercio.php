<?php

namespace App\ClaroEnvios\Comercios;

use App\ClaroEnvios\Comercios\ComerciosNiveles\ComercioNiveles;
use App\ClaroEnvios\Comercios\Direcciones\ComercioDireccion;
use App\ClaroEnvios\Comercios\Identificaciones\ComercioIdentificacion;
use App\ClaroEnvios\Comercios\Tipos\DireccionTipo;
use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class Comercio extends Model
{
    use SoftDeletesBoolean;
    protected $table = 'comercios';
    protected $fillable = ['descripcion', 'envios_promedio','updated_usuario_id','producto_tipo_id','id_as400'];
    protected $dates = ['created_at','updated_at'];

    public function address()
    {
        return $this->hasOne(ComercioDireccion::class);
    }

    public function addresses()
    {
        return $this->hasMany(ComercioDireccion::class, 'comercio_id', 'id');
    }

    public function usuario()
    {
        return $this->hasOne(User::class,'comercio_id');
    }

    public function identificaciones()
    {
        return $this->hasMany(ComercioIdentificacion::class,'id_comercio', 'id');
    }

    public function nivel()
    {
        return $this->hasOne(ComercioNiveles::class, 'id_comercio');
    }

    // public function niveleConfiguracion()
    // {
    //     //return $this->hasMany(NivelConfiguracion::class, 'id_nivel', 'id');
    //     return $this->hasManyThrough(Niveles::class, ComercioNiveles::class, 'id_nivel', 'id_nivel', 'id', 'role_id');
    // }

    public static $estatus = [
        'desactivado' => 1,
        'activo' => 0,
        'enRevision' => 2
    ];

    public static $textoEstatus = [
        1 => 'Desactivado',
        0 => 'Activo',
        2 => 'En revisión'
    ];

}