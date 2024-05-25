<?php

namespace App\ClaroEnvios\Comercios\CamposLimitesMensajerias;


use Illuminate\Database\Eloquent\Model;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class CampoLimiteMensajeria extends Model
{
    use SoftDeletesBoolean;

    protected $fillable = ['id_limite_mensajeria','id_comercio','id_mensajeria','min','max'];
    protected $table = 'campos_limites_mensajerias';
    protected  $primaryKey = 'id';
    protected $dates = ['created_at','updated_at'];


}
