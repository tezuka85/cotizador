<?php

namespace App\ClaroEnvios\Mensajerias\Configuracion;


use App\ClaroEnvios\Mensajerias\FormatoGuiaImpresionMensajeria\FormatoGuiaImpresion;
use Illuminate\Database\Eloquent\Model;

class ConfiguracionMensajeriaUsuario extends Model
{
    protected $table = 'configuraciones_mensajerias_usuarios';

    public function formatoGuiaImpresion()
    {
        return $this->belongsTo(
            FormatoGuiaImpresion::class,
            'formato_guia_impresion_id'
        );
    }
}