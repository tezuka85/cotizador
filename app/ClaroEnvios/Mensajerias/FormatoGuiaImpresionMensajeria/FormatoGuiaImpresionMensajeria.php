<?php

namespace App\ClaroEnvios\Mensajerias\FormatoGuiaImpresionMensajeria;


use Illuminate\Database\Eloquent\Model;

class FormatoGuiaImpresionMensajeria extends Model
{
    protected $table = 'formatos_guias_impresion_mensajerias';

    public function formatoGuiaImpresion()
    {
        return $this->belongsTo(
            FormatoGuiaImpresion::class,
            'formato_guia_impresion_id'
        );
    }
}