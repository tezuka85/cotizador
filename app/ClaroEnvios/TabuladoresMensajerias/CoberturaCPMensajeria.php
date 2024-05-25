<?php

namespace App\ClaroEnvios\TabuladoresMensajerias;


use Illuminate\Database\Eloquent\Model;

class CoberturaCPMensajeria extends Model
{
    protected $table = 'coberturas_cp_mensajerias';
    protected $primaryKey = 'id_coberturas_cp_mensajerias';
    protected $fillable = ['codigo_postal','id_mensajeria','created_at','updated_at'];
}