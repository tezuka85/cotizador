<?php

namespace App\ClaroEnvios\T1Paginas;




use Illuminate\Database\Eloquent\Model;

class ComercioPaquete extends Model
{

    protected $table = 'comercios_paquetes';
    protected  $primaryKey = 'id_comercios_paquetes';
    protected $dates = ['created_at','updated_at'];

    protected $fillable = ['id_paquete', 'id_comercio','created_at'];

}
