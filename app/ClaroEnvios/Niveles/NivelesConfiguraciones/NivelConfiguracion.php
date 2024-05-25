<?php

namespace App\ClaroEnvios\Niveles\NivelesConfiguraciones;

use Illuminate\Database\Eloquent\Model;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class NivelConfiguracion extends Model
{

    use SoftDeletesBoolean;
    const IS_DELETED = 'is_deleted';
    protected $primaryKey = 'id_nivel_configuracion'; 

    protected $table = 'niveles_configuraciones';
    protected $fillable = ['id_nivel_configuracion', 'id_nivel','id_mensajeria','margen','created_at'];
    protected $dates = ['created_at','updated_at'];

}