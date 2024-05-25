<?php

namespace App\ClaroEnvios\Mensajerias;


class CostoMensajeriaTO
{
    private $id;
    private $mensajeria_id;
    private $comercio_id;
    private $negociacion_id;
    private $porcentaje;
    private $costo;
    private $porcentaje_seguro;
    private $costo_adicioanl;
    private $limite_costo_envio;
    private $costo_seguro;

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
    public function getNegociacionId()
    {
        return $this->negociacion_id;
    }

    /**
     * @param mixed $negocicacion_id
     */
    public function setNegociacionId($negociacion_id): void
    {
        $this->negociacion_id = $negociacion_id;
    }

    /**
     * @return mixed
     */
    public function getMensajeriaId()
    {
        return $this->mensajeria_id;
    }

    /**
     * @param mixed $mensajeria_id
     */
    public function setMensajeriaId($mensajeria_id): void
    {
        $this->mensajeria_id = $mensajeria_id;
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

    /**
     * @return mixed
     */
    public function getCostoAdicional()
    {
        return $this->costo_adicioanl;
    }

    /**
     * @param mixed $porcentaje_seguro
     */
    public function setCostoAdicional($costo_adicional): void
    {
        $this->costo_adicioanl = $costo_adicional;
    }

    /**
     * @return mixed
     */
    public function getLimiteCostoEnvio()
    {
        return $this->limite_costo_envio;
    }

    /**
     * @param mixed $limite_costo_envio
     */
    public function setLimiteCostoEnvio($limite_costo_envio): void
    {
        $this->limite_costo_envio = $limite_costo_envio;
    }

    public function getCostoSeguro()
    {
        return $this->costo_seguro;
    }

    public function setCostoSeguro($costoSeguro): void
    {
        $this->costo_seguro = $costoSeguro;
    }
}
