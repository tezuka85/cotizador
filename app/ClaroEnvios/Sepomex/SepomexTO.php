<?php

namespace App\ClaroEnvios\Sepomex;


class SepomexTO
{
    private $d_codigo;
    private $d_asenta;
    private $d_mnpio ;
    private $d_estado;
    private $d_ciudad;
    private $c_estado;
    public function setDCodigo($dCodigo): void
    {
        $this->d_codigo = $dCodigo;
    }
    public function getDCodigo()
    {
        return $this->d_codigo;
    }
    public function setDAsenta($dAsenta): void
    {
        $this->d_asenta = $dAsenta;
    }
    public function getDAsenta()
    {
        return $this->d_asenta;
    }

    public function setDMnpio($dMnpio)
    {
        $this->d_mnpio = $dMnpio;
    }
    public function getDMnpio()
    {
        return $this->d_mnpio;
    }
    public function setDEstado($dEstado): void
    {
        $this->d_estado = $dEstado;
    }
    public function getDEstado()
    {
        return $this->d_estado;
    }
    public function setDCiudad($dCiudad): void
    {
        $this->d_ciudad = $dCiudad;
    }
    public function getDCiudad()
    {
        return $this->d_ciudad;
    }

    public function setCEstado($cEstado): void
    {
        $this->c_estado = $cEstado;
    }
    public function getCEstado()
    {
        return $this->c_estado;
    }
}
