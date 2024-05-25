<?php

namespace App\ClaroEnvios\Mensajerias\Accesos;


use Illuminate\Database\Eloquent\Model;

class AccesoMultipleMensajeria extends Model
{
    protected $table = 'accesos_multiples_mensajerias';
    protected $fillable = ['valor'];

    public function accesoCampoMensajeria()
    {
        return $this->belongsTo(
            AccesoCampoMensajeria::class,
            'id_acceso_campo_mensajeria'
        );
    }

    public static function buscarAccesosMultiplesComercios(AccesoMultipleMensajeriaTO $accesoMultipleMensajeriaTO) {
        $accesosMultipleMensajerias = AccesoMultipleMensajeria::with('accesoCampoMensajeria')
            ->where('id_comercio', $accesoMultipleMensajeriaTO->getIdComercio())
            ->get();
        //die("<pre>".print_r($accesosMultipleMensajerias->pluck('valor','accesoCampoMensajeria.clave')));
        return $accesosMultipleMensajerias;
    }

    public static function buscarAccesosMultiplesMensajerias(AccesoMultipleMensajeriaTO $accesoMultipleMensajeriaTO) {
        $accesosMultipleMensajerias = AccesoMultipleMensajeria::with('accesoCampoMensajeria')
            ->where(
                'id_mensajeria',
                $accesoMultipleMensajeriaTO->getIdMensajeria()
            )->where('id_comercio', $accesoMultipleMensajeriaTO->getComercioId())
            ->get();
        return $accesosMultipleMensajerias->pluck('valor', 'accesoCampoMensajeria.clave');
    }

    public static function buscarCampos(AccesoMultipleMensajeriaTO $accesoMultipleMensajeriaTO) {
        $accesosMultipleMensajerias = AccesoMultipleMensajeria::with('accesoCampoMensajeria')
            ->where(
                'id_mensajeria',
                $accesoMultipleMensajeriaTO->getIdMensajeria()
            )->where('id_comercio', $accesoMultipleMensajeriaTO->getIdComercio())
            ->get();
        return $accesosMultipleMensajerias;
    }
}
