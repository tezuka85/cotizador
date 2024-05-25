<?php

namespace App\ClaroEnvios\Usuarios;


class AccesoTokenUsuarioTO
{
    private $usuario_id;
    private $token;

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
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token): void
    {
        $this->token = $token;
    }

    public function toArray()
    {
        return [
            'usuario_id' => $this->usuario_id,
            'token' => $this->token
        ];
    }
}