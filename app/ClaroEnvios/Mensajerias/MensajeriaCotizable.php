<?php

namespace App\ClaroEnvios\Mensajerias;


interface MensajeriaCotizable
{
    public function rate($traerResponse = false);

    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function getTipoServicio();

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function validarCampos();

    /**
     * @return mixed
     */
    public function getResponse();

    /**
     * @param mixed $response
     */
    public function setResponse($response);

    public function verificarExcedente($response);

    public function getCodeResponse();

    public function setCodeResponse($codeResponse);
}
