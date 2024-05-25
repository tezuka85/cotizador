<?php

namespace App\ClaroEnvios\Comercios\Direcciones;


class ComercioDireccionTO
{
    private $id;
    private $comercio_id;
    private $direccion_tipo_id;
    private $estado;
    private $colonia;
    private $municipio;
    private $calle;
    private $numero;
    private $referencias;
    private $usuario_id;
    private $codigo_postal;

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id): void
    {
        $this->id = $id;
    }

    /**
     * @return mixed
     */
    public function getComercioId()
    {
        return $this->comercio_id;
    }

    /**
     * @param mixed $comercio_id
     */
    public function setComercioId($comercio_id): void
    {
        $this->comercio_id = $comercio_id;
    }

    /**
     * @return mixed
     */
    public function getDireccionTipoId()
    {
        return $this->direccion_tipo_id;
    }

    /**
     * @param mixed $direccion_tipo_id
     */
    public function setDireccionTipoId($direccion_tipo_id): void
    {
        $this->direccion_tipo_id = $direccion_tipo_id;
    }

    /**
     * @return mixed
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param mixed $estado
     */
    public function setEstado($estado): void
    {
        $this->estado = $estado;
    }

    /**
     * @return mixed
     */
    public function getColonia()
    {
        return $this->colonia;
    }

    /**
     * @param mixed $colonia
     */
    public function setColonia($colonia): void
    {
        $this->colonia = $colonia;
    }

    /**
     * @return mixed
     */
    public function getMunicipio()
    {
        return $this->municipio;
    }

    /**
     * @param mixed $municipio
     */
    public function setMunicipio($municipio): void
    {
        $this->municipio = $municipio;
    }

    /**
     * @return mixed
     */
    public function getCalle()
    {
        return $this->calle;
    }

    /**
     * @param mixed $calle
     */
    public function setCalle($calle): void
    {
        $this->calle = $calle;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero): void
    {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getReferencias()
    {
        return $this->referencias;
    }

    /**
     * @param mixed $referencias
     */
    public function setReferencias($referencias): void
    {
        $this->referencias = $referencias;
    }

    /**
     * @return mixed
     */
    public function getUsuarioId()
    {
        return $this->usuario_id;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setUsuarioId($usuario_id): void
    {
        $this->usuario_id = $usuario_id;
    }

    /**
     * @return mixed
     */
    public function getCodigoPostal()
    {
        return $this->codigo_postal;
    }

    /**
     * @param mixed $codigo_postal
     */
    public function setCodigoPostal($codigo_postal): void
    {
        $this->codigo_postal = $codigo_postal;
    }
}