<?php

namespace App\ClaroEnvios\Mensajerias\Accesos;


use Illuminate\Database\Eloquent\Model;

class AccesoCampoMensajeria extends Model
{
    protected $table = 'accesos_campos_mensajerias';

    public function accesoComercioMensajeria()
    {
        return $this->belongsTo(
            AccesoComercioMensajeria::class,
            'id','acceso_campo_mensajeria_id'
        );
    }

    public function accesoMultipleMensajeria()
    {
        return $this->belongsTo(
            AccesoMultipleMensajeria::class,
            'id','acceso_campo_mensajeria_id'
        );
    }
}
