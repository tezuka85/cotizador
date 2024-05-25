<?php

namespace App\ClaroEnvios\Mensajerias;


class EventoGuiaMensajeriaTO
{
    private $id;
    private $guia_mensajeria_id;
    private $codigo;
    private $evento;
    private $ubicacion;
    private $fecha;

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
    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param mixed $codigo
     */
    public function setCodigo($codigo): void
    {
        $this->codigo = $codigo;
    }

    /**
     * @return mixed
     */
    public function getEvento()
    {
        return $this->evento;
    }

    /**
     * @param mixed $evento
     */
    public function setEvento($evento): void
    {
        $this->evento = $evento;
    }

    /**
     * @return mixed
     */
    public function getUbicacion()
    {
        return $this->ubicacion;
    }

    /**
     * @param mixed $ubicacion
     */
    public function setUbicacion($ubicacion): void
    {
        $this->ubicacion = $ubicacion;
    }

    /**
     * @return mixed
     */
    public function getFecha()
    {
        return $this->fecha;
    }

    /**
     * @param mixed $fecha
     */
    public function setFecha($fecha): void
    {
        $this->fecha = $fecha;
    }

}
