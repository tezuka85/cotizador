<?php

namespace App\ClaroEnvios\Mensajerias\FormatoGuiaImpresionMensajeria;


class FormatoGuiaImpresionMensajeriaTO
{
    private $id;
    private $mensajeria_id;
    private $formato_guia_impresion_id;
    private $default;
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
    public function getFormatoGuiaImpresionId()
    {
        return $this->formato_guia_impresion_id;
    }

    /**
     * @param mixed $formato_guia_impresion_id
     */
    public function setFormatoGuiaImpresionId($formato_guia_impresion_id): void
    {
        $this->formato_guia_impresion_id = $formato_guia_impresion_id;
    }

    /**
     * @return mixed
     */
    public function getDefault()
    {
        return $this->default;
    }

    /**
     * @param mixed $default
     */
    public function setDefault($default): void
    {
        $this->default = $default;
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