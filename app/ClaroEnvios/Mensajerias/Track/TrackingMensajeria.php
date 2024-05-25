<?php

namespace App\ClaroEnvios\Mensajerias\Track;

use Jenssegers\Mongodb\Eloquent\Model;

class TrackingMensajeria extends Model
{
    const CREATED_AT = 'fecha_alta';
    const UPDATED_AT = 'fecha_modificacion';

    protected $connection = 'mongodb';
    protected $collection = 'tracking_mensajerias';

    public static $status = [
        'generada'=>1,
        'entregada' => 10,
        'retorno' => 5,
        'origino_retorno',4
    ];

    protected $fillable = ['_id','id_estatus', 'num_orden','id_seller','estatus_externo','codigo'];

    protected $dates = ['fecha_hora','fecha_alta','fecha_modificacion','fecha_estimada'];

    public static $statusT1Paginas = [
        1=>'Por Recolectar',
        2=>'Recolectado',
        3=>'En Transito',
        4=>'Entregado',
        5=>'Incidencia'
    ];

    public static $statusCodigos = [
       'Por Recolectar'=>['IA','OC','6','101','102','1','9','18','10'],
       'Recolectado'=>['PU','0','7','108','200','201','300','301','R','2','42','43','48','126','1004','1010','1015'],
       'En Transito'=>['ND','SC','WC','AG','FD','AR','PL','SA','DF','CC','RR','DP','OD','1','210','302','303','361','T','3','4','9','10','17','21','22','56','75','93'],
       'Entregado'=>['OK','DL','2','360','E','6','12']
    ];

    public static $statusExterno = [
        'Por Recolectar'=>['Created','In Process','DOCUMENTED'],
        'Recolectado'=>['Collected'],
        'En Transito'=>['Transit','IN PREPARATION PROCESS'],
        'Entregado'=>['Delivered','DELIVERED']
    ];

    public static $statusInterno = [
        'Created'=>'Por Recolectar',
        'In Process'=>'Por Recolectar',
        'Collected'=>'Recolectado',
        'Transit'=>'En Transito',
        'Delivered'=>'Entregado',
        'DOCUMENTED'=>'Por Recolectar',
        'IN PREPARATION PROCESS'=>'En Transito',
        'Canceled'=>'Cancelada',
    ];



}
