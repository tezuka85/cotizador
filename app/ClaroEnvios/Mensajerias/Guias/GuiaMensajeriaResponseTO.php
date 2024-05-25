<?php

namespace App\ClaroEnvios\Mensajerias\Guias;


class GuiaMensajeriaResponseTO
{
    private $id;
    public $guia_mensajeria_id;
    private $request;
    private $response;
    private $usuario_id;
    private $codigo_respuesta;

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

    public function getCodigoRespuesta()
    {
        return $this->codigo_respuesta;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setCodigoRespuesta($codigo_respuesta): void
    {
        $this->codigo_respuesta = $codigo_respuesta;
    }
}