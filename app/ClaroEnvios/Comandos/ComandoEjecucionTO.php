<?php

namespace App\ClaroEnvios\Comandos;


class ComandoEjecucionTO
{
    private $id;
    private $comando_id;
    private $fecha_inicio;
    private $fecha_fin;
    private $clase;

    /**
     * ComandoEjecucionTO constructor.
     * @param $fecha_inicio
     */
    public function __construct($parametros = [])
    {
        if (count($parametros)) {
            foreach ($parametros as $key => $value) {
                $this->{$key} = $value;
            }
        }
    }

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
    public function getComandoId()
    {
        return $this->comando_id;
    }

    /**
     * @param mixed $comando_id
     */
    public function setComandoId($comando_id): void
    {
        $this->comando_id = $comando_id;
    }

    /**
     * @return mixed
     */
    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    /**
     * @param mixed $fecha_inicio
     */
    public function setFechaInicio($fecha_inicio): void
    {
        $this->fecha_inicio = $fecha_inicio;
    }

    /**
     * @return mixed
     */
    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    /**
     * @param mixed $fecha_fin
     */
    public function setFechaFin($fecha_fin): void
    {
        $this->fecha_fin = $fecha_fin;
    }

    /**
     * @return mixed
     */
    public function getClase()
    {
        return $this->clase;
    }

    /**
     * @param mixed $clase
     */
    public function setClase($clase): void
    {
        $this->clase = $clase;
    }
}
