<?php

namespace App\ClaroEnvios\Mensajerias\Recoleccion;


class GuiaMensajeriaRecoleccionResponseTO
{
    private $id;
    private $guia_mensajeria_recoleccion_id;
    private $request;
    private $response;
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
    public function getGuiaMensajeriaRecoleccionId()
    {
        return $this->guia_mensajeria_recoleccion_id;
    }

    /**
     * @param mixed $guia_mensajeria_recoleccion_id
     */
    public function setGuiaMensajeriaRecoleccionId($guia_mensajeria_recoleccion_id): void
    {
        $this->guia_mensajeria_recoleccion_id = $guia_mensajeria_recoleccion_id;
    }

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
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
}