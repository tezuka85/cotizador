<?php

namespace App\ClaroEnvios\Sepomex;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Sepomex
 * @package App\Models
 */
class Estados extends Model
{
    protected $table = 'c_estado';

    public function obtenerSiglasEDO($codigo_postal)
    {
        return Sepomex::join("c_estado as e","e.clave","=",DB::raw("lpad(c_estado,2,'0')"))
            ->select("sepomex.d_estado","sepomex.c_estado","e.abrev as sigla")
            ->where("d_codigo","=",$codigo_postal)
            ->first();
    }

    public function buscaEstado($estado)
    {
        $cEstado = Estados::where('descripcion', $estado )->first();
        return $cEstado;
    }
}
