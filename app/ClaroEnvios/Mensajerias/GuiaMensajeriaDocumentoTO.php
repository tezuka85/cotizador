<?php

namespace App\ClaroEnvios\Mensajerias;


class GuiaMensajeriaDocumentoTO
{
    private $id;
    private $guia_mensajeria_id;
    private $usuario_id;
    private $documento;
    private $extension;
    private $ruta;

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
    public function getDocumento()
    {
        return $this->documento;
    }

    /**
     * @param mixed $documento
     */
    public function setDocumento($documento): void
    {
        $this->documento = $documento;
    }

    /**
     * @return mixed
     */
    public function getExtension()
    {
        return $this->extension;
    }

    /**
     * @param mixed $extension
     */
    public function setExtension($extension): void
    {
        $this->extension = $extension;
    }

    /**
     * @return mixed
     */
    public function getRuta()
    {
        return $this->ruta;
    }

    /**
     * @param mixed $ruta
     */
    public function setRuta($ruta): void
    {
        $this->ruta = $ruta;
    }
}
