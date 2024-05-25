<?php

namespace App\ClaroEnvios\Mensajerias\Bitacora;


class BitacoraMensajeriaDestinoTO
{
    private $id;
    private $nombre;
    private $apellidos;
    private $email;
    private $calle;
    private $numero;
    private $colonia;
    private $telefono;
    private $estado;
    private $municipio;
    private $referencias;
    private $usuario_id;
    private $nombre_comercio;

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
    public function getNombre()
    {
        return $this->nombre;
    }

    /**
     * @param mixed $nombre
     */
    public function setNombre($nombre): void
    {
        $this->nombre = $nombre;
    }

    /**
     * @return mixed
     */
    public function getApellidos()
    {
        return $this->apellidos;
    }

    /**
     * @param mixed $apellidos
     */
    public function setApellidos($apellidos): void
    {
        $this->apellidos = $apellidos;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email): void
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getCalle()
    {
        return $this->calle;
    }

    /**
     * @param mixed $calle
     */
    public function setCalle($calle): void
    {
        $this->calle = $calle;
    }

    /**
     * @return mixed
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * @param mixed $numero
     */
    public function setNumero($numero): void
    {
        $this->numero = $numero;
    }

    /**
     * @return mixed
     */
    public function getColonia()
    {
        return $this->colonia;
    }

    /**
     * @param mixed $colonia
     */
    public function setColonia($colonia): void
    {
        $this->colonia = $colonia;
    }

    /**
     * @return mixed
     */
    public function getTelefono()
    {
        return $this->telefono;
    }

    /**
     * @param mixed $telefono
     */
    public function setTelefono($telefono): void
    {
        $this->telefono = $telefono;
    }

    /**
     * @return mixed
     */
    public function getEstado()
    {
        return $this->estado;
    }

    /**
     * @param mixed $estado
     */
    public function setEstado($estado): void
    {
        $this->estado = $estado;
    }

    /**
     * @return mixed
     */
    public function getMunicipio()
    {
        return $this->municipio;
    }

    /**
     * @param mixed $municipio
     */
    public function setMunicipio($municipio): void
    {
        $this->municipio = $municipio;
    }

    /**
     * @return mixed
     */
    public function getReferencias()
    {
        return $this->referencias;
    }

    /**
     * @param mixed $referencias
     */
    public function setReferencias($referencias): void
    {
        $this->referencias = $referencias;
    }

    public function setDatosArray(array $array)
    {
        $this->nombre = $array['nombre_destino'];
        $this->apellidos = $array['apellidos_destino'];
        $this->email = $array['email_destino'];
        $this->calle = $array['calle_destino'];
        $this->numero = $array['numero_destino'];
        $this->colonia = $array['colonia_destino'];
        $this->telefono = $array['telefono_destino'];
        $this->estado = $array['estado_destino'];
        $this->municipio = $array['municipio_destino'];
        $this->referencias = $array['referencias_destino'];
        $this->nombre_comercio = array_key_exists('nombre_comercio_destino',$array)?$array['nombre_comercio_destino']:null;
       // die(var_dump($this->nombre_comercio));
    }

    public function setDatos(array $array)
    {
        $this->nombre = $array['nombre'];
        $this->apellidos = $array['apellidos'];
        $this->email = $array['email'];
        $this->calle = $array['calle'];
        $this->numero = $array['numero'];
        $this->colonia = $array['colonia'];
        $this->telefono = $array['telefono'];
        $this->estado = $array['estado'];
        $this->municipio = $array['municipio'];
        $this->referencias = $array['referencias'];
        $this->nombre_comercio = array_key_exists('nombre_comercio_destino',$array)?$array['nombre_comercio_destino']:null;
    }

    public function getDireccionCompuesta()
    {
        return $this->calle.' '.$this->numero;
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
    public function getNombreComercio()
    {
        return $this->nombre_comercio;
    }

    /**
     * @param mixed $nombre_comercio
     */
    public function setNombreComercio($nombreComercio): void
    {
        $this->nombre_comercio = $nombreComercio;
    }
}
