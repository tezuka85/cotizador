<?php

namespace App\ClaroEnvios\Mensajerias;


class CostoMensajeriaPorcentajeTO
{
    private $id;
    private $mensajeria_id;
    private $comercio_id;
    private $porcentaje;
    private $porcentaje_seguro;
    private $costo;
    private $costo_mensajeria_id;
    private $usuario_id;

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
    public function getMensajeriaId()
    {
        return $this->mensajeria_id;
    }

    /**
     * @param mixed $mensaje_id
     */
    public function setMensajeriaId($mensajeria_id): void
    {
        $this->mensajeria_id = $mensajeria_id;
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
}
