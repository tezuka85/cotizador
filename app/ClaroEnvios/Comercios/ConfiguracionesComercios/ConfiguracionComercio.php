<?php

namespace App\ClaroEnvios\Comercios\ConfiguracionesComercios;

use Illuminate\Database\Eloquent\Model;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class ConfiguracionComercio extends Model
{
    use SoftDeletesBoolean;
    
    protected $table = 'configuraciones_comercios';
    protected $fillable = ['id', 'nombre','is_deleted','created_at'];
    protected $dates = ['created_at','created_update'];

    public static $comerciosPrepago = [2,5,6,9];
    public static $comerciosZonas = [3,4,7,8];
    public static $comerciosCredito = [2,5,8];
    public static $comerciosCustom = [5,6];

}