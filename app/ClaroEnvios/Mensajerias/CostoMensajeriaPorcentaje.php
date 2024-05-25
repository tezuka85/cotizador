<?php
namespace App\ClaroEnvios\Mensajerias;


use Illuminate\Database\Eloquent\Model;

class CostoMensajeriaPorcentaje extends Model
{
    protected $table = 'costos_mensajerias_porcentajes';

    protected $fillable = ['costo'];
}
