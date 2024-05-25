<?php

namespace App\ClaroEnvios\T1Paginas;




use Jenssegers\Mongodb\Eloquent\Model;

class HistoricoConsumoGuia extends Model
{
    protected $connection = 'mongodbSIF';
    protected $collection = 'historico_consumo_guias';
    protected $primaryKey = '_id';

    public $timestamps = false;
    protected $dates = ['fecha_guia','fecha_alta','fecha_modificacion'];

}