<?php
namespace App\ClaroEnvios\Mensajerias;


use Illuminate\Database\Eloquent\Model;

class GuiaMensajeriaDocumento extends Model
{
    protected $table = 'guias_mensajerias_documentos';

    public function getDocumentoAttribute()
    {
        return utf8_decode($this->attributes['documento']);
    }

    public function setDocumentoAttribute($documento)
    {
        $this->attributes['documento'] = utf8_encode((string)$documento);
    }
}
