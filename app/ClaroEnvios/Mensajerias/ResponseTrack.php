<?php

namespace App\ClaroEnvios\Mensajerias;

class ResponseTrack
{
    private $request;
    private $response;
    private $track;
    private $actualiza = true;

    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

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
     * @param mixed $codigo_postal_destino
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }

    /**
     * @return mixed
     */
    public function getTrack()
    {
        return $this->track;
    }

    /**
     * @param mixed $peso
     */
    public function setTrack($track): void
    {
        $this->track = $track;
    }

    /**
     * @return mixed
     */
    public function getActualiza()
    {
        return $this->actualiza;
    }

    /**
     * @param mixed $peso
     */
    public function setActualiza($actualiza): void
    {
        $this->actualiza = $actualiza;
    }

}
