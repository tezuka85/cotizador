<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestino;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigen;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\Recoleccion\GuiaMensajeriaRecoleccion;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Mail;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class GuiaMensajeria extends Model
{
    use SoftDeletesBoolean;
   // const IS_DELETED = 'estatus';
    protected $table = 'guias_mensajerias';
    protected $fillable = ['id','estatus','guia'];
    protected $primaryKey = 'id';

    protected $hidden = [
        'updated_at',
        'usuario_id',
        'updated_usuario_id'
    ];

    public function bitacoraCotizacionMensajeria()
    {
        return $this->belongsTo(BitacoraCotizacionMensajeria::class);
    }

    public function bitacoraMensajeriaDestino()
    {
        return $this->belongsTo(BitacoraMensajeriaDestino::class);
    }

    public function bitacoraMensajeriaOrigen()
    {
        return $this->belongsTo(BitacoraMensajeriaOrigen::class);
    }

    public function envioMail($filePath)
    {
        //die("<pre>".print_r($filePath));
        $guiaMensajeria = $this;
        $mensajeria = $guiaMensajeria
            ->bitacoraCotizacionMensajeria
            ->mensajeria;
        Mail::send(
            'mensajerias.guias.mail.envio_guia',
            [],
            function ($mail) use ($filePath, $guiaMensajeria, $mensajeria) {
                $mail->from('maria.policarpo@claroshop.com');
                $mail->to('maria.policarpo@claroshop.com')
                     ->subject(
                         'Servicio de envio de guia - '
                         .$guiaMensajeria->guia.' - '.$mensajeria->descripcion
                     );
                $mail->attach($filePath);
            }
        );
    }

    public function eventosGuiasMensajerias()
    {
        return $this->hasMany(EventoGuiaMensajeria::class, 'guia_mensajeria_id');
    }

    public function guiaMensajeriaRecoleccion()
    {
        return $this->belongsTo(
            GuiaMensajeriaRecoleccion::class,
            'id',
            'guia_mensajeria_id'
        );
    }

    public static $status = [
        'generada'=>1,
        'entregada' => 10,
        'retorno' => 3,
        'origino_retorno' => 4,
        'entregada_retorno' => 5,
    ];

    public static $origenGuia = [
        'apiT1envios' =>0,//zonas, dax, pot, axii, ss,onest multi-seller
        't1envios'=>1,//t1envios.com
        't1comercios' => 2,//t1comercios.com
        't1paginas' => 3,//t1paginas.com
        'ONEST' => 4,//onest, david
        'shopify' => 5,//onest, david

    ];

    public static $statusGuia = [
        'activa'=>1,
        'cancelada' => 2
    ];

    public static $origenGuiaTexto = [
        0 => 'apiT1envios',
        1 => 't1envios',
        2 => 't1comercios',
        3 => 't1paginas',
        4 => 'ONEST',
        5 => 'shopify'
    ];
}
