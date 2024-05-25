<?php

namespace App\ClaroEnvios\Mensajerias\Recoleccion;


class GuiaMensajeriaRecoleccionTO
{
    private $id;
    private $guia_mensajeria_id;
    private $pick_up;
    private $localizacion;
    private $usuario_id;
    private $guiaMensajeriaRecoleccionResponseTO;
    private $fecha_recoleccion;

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
    public function getGuiaMensajeriaId()
    {
        return $this->guia_mensajeria_id;
    }

    /**
     * @param mixed $guia_mensajeria_id
     */
    public function setGuiaMensajeriaId($guia_mensajeria_id): void
    {
        $this->guia_mensajeria_id = $guia_mensajeria_id;
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
    public function getLocalizacion()
    {
        return $this->localizacion;
    }

    /**
     * @param mixed $localizacion
     */
    public function setLocalizacion($localizacion): void
    {
        $this->localizacion = $localizacion;
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
    public function getGuiaMensajeriaRecoleccionResponseTO()
    {
        return $this->guiaMensajeriaRecoleccionResponseTO;
    }

    /**
     * @param mixed $guiaMensajeriaRecoleccionResponseTO
     */
    public function setGuiaMensajeriaRecoleccionResponseTO($guiaMensajeriaRecoleccionResponseTO): void
    {
        $this->guiaMensajeriaRecoleccionResponseTO = $guiaMensajeriaRecoleccionResponseTO;
    }

    public function setFechaRecoleccion($fechaRecoleccion): void
    {
        $this->fecha_recoleccion = $fechaRecoleccion;
    }

    public function getFechaRecoleccion()
    {
        return $this->fecha_recoleccion;
    }

}