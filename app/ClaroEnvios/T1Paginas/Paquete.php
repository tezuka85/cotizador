<?php

namespace App\ClaroEnvios\T1Paginas;




use Illuminate\Database\Eloquent\Model;

class Paquete extends Model
{

    protected $table = 'paquetes';
    protected  $primaryKey = 'id_paquete';
    protected $dates = ['created_at','updated_at'];

    protected $fillable = ['id_paquete', 'nombre','estatus','created_at'];

}
