<?php

namespace App\ClaroEnvios\Comercios;


class ComercioTO
{
    private $id;
    private $clave;
    private $descripcion;
    private $usuario_id;
    private $comercioDirecciones;
    private $envios_promedio;
    private $producto_tipo_id;
    private $tipo_empresa;
    private $id_negociacion;
    private $id_configuracion;
    private $rfc;

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
    public function getClave()
    {
        return $this->clave;
    }

    /**
     * @param mixed $clave
     */
    public function setClave($clave): void
    {
        $this->clave = $clave;
    }

    /**
     * @return mixed
     */
    public function getDescripcion()
    {
        return $this->descripcion;
    }

    /**
     * @param mixed $descripcion
     */
    public function setDescripcion($descripcion): void
    {
        $this->descripcion = $descripcion;
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
    public function getComercioDirecciones()
    {
        return $this->comercioDirecciones;
    }

    /**
     * @param mixed $comercioDireccionTO
     */
    public function setComercioDirecciones(array $comercioDirecciones): void
    {
        $this->comercioDirecciones = $comercioDirecciones;
    }

    /**
     * @return mixed
     */
    public function getEnviosPromedio()
    {
        return $this->envios_promedio;
    }

    /**
     * @param mixed $envios_promedio
     */
    public function setEnviosPromedio($envios_promedio): void
    {
        $this->envios_promedio = $envios_promedio;
    }

    /**
     * @return mixed
     */
    public function getTipoEmpresa()
    {
        return $this->tipo_empresa;
    }

    /**
     * @param mixed $tipo_empresa
     */
    public function setTipoEmpresa($tipo_empresa): void
    {
        $this->tipo_empresa = $tipo_empresa;
    }

    /**
     * @return mixed
     */
    public function getProductoTipoId()
    {
        return $this->producto_tipo_id;
    }

    /**
     * @param mixed $producto_tipo_id
     */
    public function setProductoTipoId($producto_tipo_id): void
    {
        $this->producto_tipo_id = $producto_tipo_id;
    }

    /**
     * @return mixed
     */
    public function getIdNegociacion()
    {
        return $this->id_negociacion;
    }

    /**
     * @param mixed $id_negociacion
     */
    public function setIdNegociacion($id_negociacion): void
    {
        $this->id_negociacion = $id_negociacion;
    }

    public function getIdConfiguracion()
    {
        return $this->id_configuracion;
    }

    /**
     * @param mixed $id_configuracion
     */
    public function setIdConfiguracion($id_configuracion): void
    {
        $this->id_configuracion = $id_configuracion;
    }

    public function getRFC()
    {
        return $this->rfc;
    }


    public function setRFC($rfc): void
    {
        $this->rfc = $rfc;
    }


}