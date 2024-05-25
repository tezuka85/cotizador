<?php

namespace App\ClaroEnvios\Mensajerias;


class ConfiguracionMensajeriaTO
{
    private $id;
    private $mensajeria_id;
    private $comercio_id;
    private $negociacion_id;
    private $tipo_configuracion;
    private $tipo_calculo;
    private $porcentaje_seguro;

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
     * @param $mensajeria_id
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
     * @param $comercio_id
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
    public function getTipoConfiguracion()
    {
        return $this->tipo_configuracion;
    }

    /**
     * @param $tipo_configuracion
     */
    public function setTipoConfiguracion($tipo_configuracion): void
    {
        $this->tipo_configuracion = $tipo_configuracion;
    }

    /**
     * @return mixed
     */
    public function getTipoCalculo()
    {
        return $this->tipo_calculo;
    }

    /**
     * @param $tipo_calculo
     */
    public function setTipoCalculo($tipo_calculo): void
    {
        $this->tipo_calculo = $tipo_calculo;
    }

    /**
     * @return mixed
     */
    public function getPorcentajeSeguro()
    {
        return $this->porcentaje_seguro;
    }

    /**
     * @param $tipo_calculo
     */
    public function setTPorcentajeSeguro($porcentaje_seguro): void
    {
        $this->porcentaje_seguro = $porcentaje_seguro;
    }

    public static $tipoConfiguracion = [
        'claroEnvios' => 1,
        'comercio' => 2
    ];

    public static $tipoNegociacion = [
        'claroEnvios' => 1,
        'comercio' => 2
    ];

    public static $tipoCalculo = [
        'porcentaje' => 'p',
        'comercio' => 'c'
    ];

}
