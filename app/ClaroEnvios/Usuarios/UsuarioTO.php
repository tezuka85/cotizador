<?php

namespace App\ClaroEnvios\Usuarios;


use Illuminate\Support\Facades\Hash;

class UsuarioTO
{
    private $id;
    private $nombres;
    private $email;
    private $departamento_id;
    private $password;
    private $token;
    private $first;
    private $apellidos;
    private $comercioTO;
    private $comercio_id;
    private $role;

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
    public function getNombres()
    {
        return $this->nombres;
    }

    /**
     * @param mixed $nombres
     */
    public function setNombres($nombres): void
    {
        $this->nombres = $nombres;
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
    public function getDepartamentoId()
    {
        return $this->departamento_id;
    }

    /**
     * @param mixed $departamento_id
     */
    public function setDepartamentoId($departamento_id): void
    {
        $this->departamento_id = $departamento_id;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password, $hash = true): void
    {
        $this->password = $hash ? Hash::make($password) : $password;
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

    /**
     * @return mixed
     */
    public function getFirst()
    {
        return $this->first;
    }

    /**
     * @param mixed $first
     */
    public function setFirst($first): void
    {
        $this->first = $first;
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
    public function getComercioTO()
    {
        return $this->comercioTO;
    }

    /**
     * @param mixed $comercioTO
     */
    public function setComercioTO($comercioTO): void
    {
        $this->comercioTO = $comercioTO;
    }

    /**
     * @return mixed
     */
    public function getComercioId()
    {
        return $this->comercio_id;
    }

    /**
     * @param mixed $comercio_id
     */
    public function setComercioId($comercio_id): void
    {
        $this->comercio_id = $comercio_id;
    }


    public function setRole($role): void
    {
        $this->role = $role;
    }

    public function getRole()
    {
        return $this->role;
    }
}
