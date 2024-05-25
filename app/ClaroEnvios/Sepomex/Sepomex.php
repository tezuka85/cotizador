<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 25/05/18
 * Time: 06:58 PM
 */

namespace App\ClaroEnvios\Sepomex;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class Sepomex
 * @package App\Models
 */
class Sepomex extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'sepomex';

    public function buscarCP($codigo_postal)
    {
        return Sepomex::where("d_codigo","=",$codigo_postal)->first();
    }

    public function obtenerSiglasEDO($codigo_postal)
    {
        return Sepomex::join("c_estado as e","e.clave","=",DB::raw("lpad(c_estado,2,'0')"))
            ->select("sepomex.d_estado","sepomex.c_estado","e.abrev as sigla")
            ->where("d_codigo","=",$codigo_postal)
            ->first();
    }

    public function obtenerEstados($codigo_postal)
    {
        return Sepomex::join("c_estado as e","e.clave","=",DB::raw("lpad(c_estado,2,'0')"))
            ->select("sepomex.d_estado","sepomex.c_estado","e.abrev as sigla")
            ->where("d_codigo","=",$codigo_postal)
            ->first();
    }

    public function zona(){
        return $this->hasOne(CodigoPostalZona::class, 'codigo_postal');
    }

    public function nuevo(SepomexTO $sepomexTO){
        Log::info('Entra a guardar nuevo codigo postal '.$sepomexTO->getDCodigo());
        Log::info('--------------------------------');
//        die(print_r($sepomexTO));
        $sepomex = new Sepomex();
        $sepomex->d_codigo = $sepomexTO->getDCodigo();
        $sepomex->d_asenta=  $sepomexTO->getDAsenta();
        $sepomex->d_tipo_asenta = '';
        $sepomex->d_mnpio =  $sepomexTO->getDMnpio();
        $sepomex->d_estado =  $sepomexTO->getDEstado();
        $sepomex->d_ciudad =  $sepomexTO->getDCiudad();
        $sepomex->d_cp = '';
        $sepomex->c_estado = $sepomexTO->getCEstado();
        $sepomex->c_oficina ='';
        $sepomex->c_cp = '';
        $sepomex->c_tipo_asenta = '';
        $sepomex->c_mnpio = '';
        $sepomex->d_zona = '';
        $fecha = Carbon::now();
        $sepomex->fecha_alta = $fecha->format('Y-m-d H:i:s');
        $sepomex->save();
    }

}
