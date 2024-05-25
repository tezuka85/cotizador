<?php

namespace App\ClaroEnvios\Mensajerias\GuiaExcedente;


use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use Illuminate\Database\Eloquent\Model;

class GuiaExcedente extends Model
{
    protected $table = 'guias_excedentes';

    public function bitacoraCotizacion()
    {
        return $this->hasOne(BitacoraCotizacionMensajeria::class);
    }
}