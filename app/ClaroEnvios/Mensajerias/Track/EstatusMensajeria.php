<?php

namespace App\ClaroEnvios\Mensajerias\Track;

use Jenssegers\Mongodb\Eloquent\Model;

class EstatusMensajeria extends Model
{
    const CREATED_AT = 'fecha_alta';
    const UPDATED_AT = 'fecha_modificacion';

    protected $connection = 'mongodb';
    protected $collection = 'estatus_mensajeria';

    public static $status = [
        'generada'=>1,
        'entregada' => 10
    ];

    protected $fillable = ['_id','id_mensajeria', 'guia','codigo','estatus'];

    protected $dates = ['fecha_alta','fecha_modificacion'];

    public function estatus() {

        return $this->hasOne(TrackingMensajeria::class, 'guia', 'guia');
    }

}
