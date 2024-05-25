<?php

namespace App\ClaroEnvios\Mensajerias\Recoleccion;


class MensajeriaRecoleccionTO
{
    private $id;
    private $pick_up;
    private $usuario_id;
    private $datos;
    private $mensajeria;
    private $negociacion_id;
    private $localizacion;
    private $comercio_id;
    private $guias;
    private $fecha_recoleccion;
    private $siglas_codigo_origen;
    private $hora_inicio;
    private $hora_fin;

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
    public function getPickUp()
    {
        return $this->pick_up;
    }

    /**
     * @param mixed $pick_up
     */
    public function setPickUp($pick_up): void
    {
        $this->pick_up = $pick_up;
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

    public function getDatos()
    {
        return $this->datos;
    }


    public function setDatos($datos): void
    {
        $this->datos = $datos;
    }

    public function getmensajeria()
    {
        return $this->mensajeria;
    }

    /**
     * @param mixed $mensajeria_id_id
     */
    public function setMensajeria($mensajeria): void
    {
        $this->mensajeria = $mensajeria;
    }

    public function getNegociacionId()
    {
        return $this->negociacion_id;
    }

    /**
     * @param mixed $mensajeria_id_id
     */
    public function setNegociacionId($negociacionId): void
    {
        $this->negociacion_id = $negociacionId;
    }


    public function getLocalizacion()
    {
        return $this->localizacion;
    }

    /**
     * @param mixed $mensajeria_id_id
     */
    public function setLocalizacion($localizacion): void
    {
        $this->localizacion = $localizacion;
    }

    public function getComercioId()
    {
        return $this->comercio_id;
    }

    /**
     * @param mixed $mensajeria_id_id
     */
    public function setComercioId($comercioId): void
    {
        $this->comercio_id = $comercioId;
    }

    public function setSiglasCodigoOrigen($siglasCodigoOrigen)
    {
        $this->siglas_codigo_origen = $siglasCodigoOrigen;
    }

    public function getSiglasCodigoOrigen()
    {
       return $this->siglas_codigo_origen;
    }

    public function setHoraInicio($horaInicio)
    {
        $this->hora_inicio = $horaInicio;
    }

    public function getHoraInicio()
    {
       return $this->hora_inicio;
    }

    public function setHoraFin($horaFin)
    {
        $this->hora_fin = $horaFin;
    }

    public function getHoraFin()
    {
       return $this->hora_fin;
    }

    public function getGuias()
    {
        return $this->guias;
    }

    /**
     * @param mixed $guias
     */
    public function setGuias($guias): void
    {
        $this->guias = $guias;
    }

    public function getFechaRecoleccion()
    {
        return $this->fecha_recoleccion;
    }

    /**
     * @param mixed $guia
     */
    public function setFechaRecoleccion($fechaRecoleccion): void
    {
        $this->fecha_recoleccion = $fechaRecoleccion;
    }
}