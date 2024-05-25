<?php

namespace App\ClaroEnvios\Comercios\ComerciosNiveles;

use Illuminate\Database\Eloquent\Model;

class ComercioNiveles extends Model
{
    protected $table = 'comercios_niveles';
    protected $fillable = ['id_comercio_nivel', 'id_nivel','id_comercio','created_at','updated_usuario_id'];
    protected $dates = ['created_at','created_update'];
}
