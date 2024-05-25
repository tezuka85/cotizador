<?php

namespace App\ClaroEnvios\Mensajerias\Accesos;


class AccesoComercioMensajeriaTO
{
    private $id;
    private $acceso_campo_mensajeria_id;
    private $mensajeria_id;
    private $comercio_id;
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
    public function getAccesoCampoMensajeriaId()
    {
        return $this->acceso_campo_mensajeria_id;
    }

    /**
     * @param mixed $acceso_campo_mensajeria_id
     */
    public function setAccesoCampoMensajeriaId($acceso_campo_mensajeria_id): void
    {
        $this->acceso_campo_mensajeria_id = $acceso_campo_mensajeria_id;
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
}