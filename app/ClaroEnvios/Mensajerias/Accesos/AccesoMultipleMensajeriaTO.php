<?php

namespace App\ClaroEnvios\Mensajerias\Accesos;


class AccesoMultipleMensajeriaTO
{
    private $id;
    private $id_acceso_campo_mensajeria;
    private $id_mensajeria;
    private $id_servicio;
    private $id_comercio;
    private $valor;

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
    public function getIdAccesoCampoMensajeria()
    {
        return $this->id_acceso_campo_mensajeria;
    }

    /**
     * @param mixed $acceso_campo_mensajeria_id
     */
    public function setIdAccesoCampoMensajeria($id_acceso_campo_mensajeria): void
    {
        $this->id_acceso_campo_mensajeria = $id_acceso_campo_mensajeria;
    }

    /**
     * @return mixed
     */
    public function getIdMensajeria()
    {
        return $this->id_mensajeria;
    }

    /**
     * @param mixed $id_mensajeria
     */
    public function setIdMensajeria($id_mensajeria): void
    {
        $this->id_mensajeria = $id_mensajeria;
    }

    /**
     * @return mixed
     */
    public function getIdComercio()
    {
        return $this->id_comercio;
    }

    /**
     * @param mixed $id_comercio
     */
    public function setIdComercio($id_comercio): void
    {
        $this->id_comercio = $id_comercio;
    }

    /**
     * @return mixed
     */
    public function getValor()
    {
        return $this->valor;
    }

    /**
     * @param mixed $valor
     */
    public function setValor($valor): void
    {
        $this->valor = $valor;
    }

    public function getIdServicio()
    {
        return $this->id_servicio;
    }

    /**
     * @param mixed $id_servicio
     */
    public function setIdServicio($id_servicio): void
    {
        $this->id_servicio = $id_servicio;
    }
}
