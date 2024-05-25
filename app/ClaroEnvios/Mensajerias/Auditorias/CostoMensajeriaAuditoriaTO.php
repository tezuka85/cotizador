<?php

namespace App\ClaroEnvios\Mensajerias\Auditorias;


class CostoMensajeriaAuditoriaTO
{
    private $id;
    private $costo_mensajeria_id;
    private $comercio_id;
    private $negociacion_id;
    private $mensajeria_id;
    private $porcentaje;
    private $costo;
    private $porcentaje_seguro;
    private $usuario_id;
    private $updated_usuario_id;
    private $funcion;

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
    public function getCostoMensajeriaId()
    {
        return $this->costo_mensajeria_id;
    }

    /**
     * @param mixed $costo_mensajeria_id
     */
    public function setCostoMensajeriaId($costo_mensajeria_id): void
    {
        $this->costo_mensajeria_id = $costo_mensajeria_id;
    }

    /**
     * @return mixed
     */
    public function getNegociacionId()
    {
        return $this->negociacion_id;
    }

    /**
     * @param mixed $negociacion_id
     */
    public function setNegociacionId($negociacion_id): void
    {
        $this->negociacion_id = $negociacion_id;
    }

    /**
     * @return mixed
     */
    public function getComercioId()
    {
        return $this->comercio_id;
    }

    /**
     * @param mixed $asignacion_multiple
     */
    public function setComercioId($comercio_id): void
    {
        $this->comercio_id = $comercio_id;
    }

    /**
     * @return mixed
     */
    public function getPorcentaje()
    {
        return $this->porcentaje;
    }

    /**
     * @param mixed $porcentaje
     */
    public function setPorcentaje($porcentaje): void
    {
        $this->porcentaje = $porcentaje;
    }

    /**
     * @return mixed
     */
    public function getCosto()
    {
        return $this->costo;
    }

    /**
     * @param mixed $costo
     */
    public function setCosto($costo): void
    {
        $this->costo = $costo;
    }

    /**
     * @return mixed
     */
    public function getPorcentajeSeguro()
    {
        return $this->porcentaje_seguro;
    }

    /**
     * @param mixed $porcentaje_seguro
     */
    public function setPorcentajeSeguro($porcentaje_seguro): void
    {
        $this->porcentaje_seguro = $porcentaje_seguro;
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
    public function getUpdatedUsuarioId()
    {
        return $this->updated_usuario_id;
    }

    /**
     * @param mixed $updated_usuario_id
     */
    public function setUpdatedUsuarioId($updated_usuario_id): void
    {
        $this->updated_usuario_id = $updated_usuario_id;
    }

    public function setDatosCostoMensajeria(\App\ClaroEnvios\Mensajerias\CostoMensajeria $costoMensajeria)
    {
        $this->costo_mensajeria_id = $costoMensajeria->id;
        $this->comercio_id = $costoMensajeria->comercio_id;
        $this->negociacion_id = $costoMensajeria->negociacion_id;
        $this->mensajeria_id = $costoMensajeria->mensajeria_id;
        $this->porcentaje = $costoMensajeria->porcentaje;
        $this->porcentaje_seguro = $costoMensajeria->porcentaje_seguro;
        $this->costo = $costoMensajeria->costo;
        $this->usuario_id = $costoMensajeria->usuario_id;

    }

    /**
     * @return mixed
     */
    public function getMensajeriaId()
    {
        return $this->mensajeria_id;
    }

    /**
     * @param mixed $cuenta_tipo_id
     */
    public function setMensajeriaId($mensajeria_id): void
    {
        $this->mensajeria_id = $mensajeria_id;
    }

    /**
     * @return mixed
     */
    public function getFuncion()
    {
        return $this->funcion;
    }

    /**
     * @param mixed $funcion
     */
    public function setFuncion($funcion): void
    {
        $this->funcion = $funcion;
    }
}