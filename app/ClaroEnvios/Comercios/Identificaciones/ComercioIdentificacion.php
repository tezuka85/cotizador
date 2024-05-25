<?php

namespace App\ClaroEnvios\Comercios\Identificaciones;


use Illuminate\Database\Eloquent\Model;

class ComercioIdentificacion extends Model
{
    const CREATED_AT = 'fecha_alta';
    const UPDATED_AT = 'fecha_modificacion';

    protected $table = 'comercios_identificaciones';
    protected $fillable = ['id_comercio','url','fecha_alta','fecha_modificacion'];



}