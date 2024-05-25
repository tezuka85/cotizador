<?php

namespace App\ClaroEnvios\Mensajerias\BitacoraCotizacion;


class BitacoraCotizacionMensajeriaResponseTO
{
    private $id;
    private $request;
    private $response;
    private $usuario_id;
    private $bitacora_cotizacion_mensajeria_id;
    private $numero_externo;
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

    /**
     * @return mixed
     */
    public function getBitacoraCotizacionMensajeriaId()
    {
        return $this->bitacora_cotizacion_mensajeria_id;
    }

    /**
     * @param mixed $bitacora_cotizacion_mensajeria_id
     */
    public function setBitacoraCotizacionMensajeriaId($bitacora_cotizacion_mensajeria_id): void
    {
        $this->bitacora_cotizacion_mensajeria_id = $bitacora_cotizacion_mensajeria_id;
    }

    public function setNumeroExterno($numeroExterno){
        $this->numero_externo = $numeroExterno;
    }

    public function getNumeroExterno(){
        return $this->numero_externo;
    }

    public function setCodigoRespuesta($codigoRespuesta){
        $this->codigo_respuesta = $codigoRespuesta;
    }

    public function getCodigoRespuesta(){
        return $this->codigo_respuesta;
    }
}
