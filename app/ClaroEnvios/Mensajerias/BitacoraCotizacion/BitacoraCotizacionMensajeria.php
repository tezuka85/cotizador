<?php

namespace App\ClaroEnvios\Mensajerias\BitacoraCotizacion;


use App\ClaroEnvios\Mensajerias\GuiaMensajeria;
use App\ClaroEnvios\Mensajerias\Mensajeria;
use Illuminate\Database\Eloquent\Model;
use Dirape\Token\DirapeToken;

class BitacoraCotizacionMensajeria extends Model
{
    use DirapeToken;

    protected $table = 'bitacoras_cotizaciones_mensajerias';
    protected $DT_Column='token';
    protected $DT_settings=['type'=>DT_Unique,'size'=>60,'special_chr'=>false];

    public function mensajeria()
    {
        return $this->belongsTo(Mensajeria::class);
    }

    public function bitacorasCotizacionesMensajerias()
    {
        return $this->hasMany(
            GuiaMensajeria::class,
            'bitacora_cotizacion_mensajeria_id'
        );
    }

    public function paquete()
    {
        return $this->hasOne(CotizacionPaquete::class, 'id_bitacora_cotizacion');
    }
}
