<?php

namespace App\ClaroEnvios\Mensajerias\Accesos;


use Illuminate\Database\Eloquent\Model;

class AccesoComercioMensajeria extends Model
{
    protected $table = 'accesos_comercios_mensajerias';
    protected $fillable = ['valor'];
    public $timestamps = false;

    public function accesoCampoMensajeria()
    {
        return $this->belongsTo(
            AccesoCampoMensajeria::class,
            'acceso_campo_mensajeria_id'
        );
    }

    public static function buscarAccesosComercios(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTO) {
        $accesosComerciosMensajerias = AccesoComercioMensajeria::with('accesoCampoMensajeria')
            ->where('comercio_id', $accesoComercioMensajeriaTO->getComercioId())
            ->get();
       // die("<pre>".print_r($accesosComerciosMensajerias->pluck('valor', 'accesoCampoMensajeria.clave')));
        return $accesosComerciosMensajerias;
    }

    public static function buscarAccesosComerciosMensajerias(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTO) {
        $accesosComerciosMensajerias = AccesoComercioMensajeria::with('accesoCampoMensajeria')
            ->where(
                'mensajeria_id',
                $accesoComercioMensajeriaTO->getMensajeriaId()
            )->where('comercio_id', $accesoComercioMensajeriaTO->getComercioId())
            ->get();
        return $accesosComerciosMensajerias->pluck('valor', 'accesoCampoMensajeria.clave');
    }

    public static function buscarCampos(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTO) {
        $accesosComerciosMensajerias = AccesoComercioMensajeria::with('accesoCampoMensajeria')
            ->where(
                'mensajeria_id',
                $accesoComercioMensajeriaTO->getMensajeriaId()
            )->where('comercio_id', $accesoComercioMensajeriaTO->getComercioId())
            ->get();
        return $accesosComerciosMensajerias;
    }
}