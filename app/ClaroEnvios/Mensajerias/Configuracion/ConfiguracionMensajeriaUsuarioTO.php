<?php

namespace App\ClaroEnvios\Mensajerias\Configuracion;


class ConfiguracionMensajeriaUsuarioTO
{
    private $id;
    private $mensajeria_id;
    private $formato_guia_impresion_id;
    private $usuario_id;
    private $updated_usuario_id;

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
    public function getUpdatedUsuarioId()
    {
        return $this->updated_usuario_id;
    }

    /**
     * @param mixed $updated_usuario_id
     */
    public function setUpdatedUsuarioId($updated_usuario_id): void
    {
        $this->updated_usuario_id = $updated_usuario_id;
    }
}