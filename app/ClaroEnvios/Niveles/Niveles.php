<?php

namespace App\ClaroEnvios\Niveles;

use Illuminate\Database\Eloquent\Model;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class Niveles extends Model
{

    use SoftDeletesBoolean;
    const IS_DELETED = 'estatus';
    protected $table = 'niveles';
    protected $fillable = ['id_nivel', 'nombre','created_at'];
    protected $dates = ['created_at','updated_at'];

}