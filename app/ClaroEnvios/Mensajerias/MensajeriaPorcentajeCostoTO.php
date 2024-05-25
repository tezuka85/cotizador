<?php

namespace App\ClaroEnvios\Mensajerias;


class MensajeriaPorcentajeCostoTO
{
    private $id;
    private $configuracion_id;
    private $porcentaje;
    private $costo;

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
    public function getConfiguracionId()
    {
        return $this->configuracion_id;
    }

    /**
     * @param $mensajeria_id
     */
    public function setConfiguracionId($confguracion_id): void
    {
        $this->configuracion_id = $confguracion_id;
    }


    /**
     * @return mixed
     */
    public function getPorcentaje()
    {
        return $this->porcentaje;
    }

    /**
     * @param $comercio_id
     */
    public function setPorcentaje($porentaje): void
    {
        $this->porcentaje = $porentaje;
    }

    /**
     * @return mixed
     */
    public function getCosto()
    {
        return $this->costo;
    }
}
