<?php

namespace App\ClaroEnvios\Mensajerias\GuiasInternacionales;


use Illuminate\Database\Eloquent\Model;

class GuiaInternacional extends Model
{
    protected $table = 'guias_internacionales';
    protected $dates = ['fecha_alta','fecha_modificacion'];

    public $timestamps = false;
}