<?php

namespace App\ClaroEnvios\Comandos;


class ComandoTO
{
    private $id;
    private $comando;
    private $clase;
    private $first;

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
    public function getComando()
    {
        return $this->comando;
    }

    /**
     * @param mixed $comando
     */
    public function setComando($comando): void
    {
        $this->comando = $comando;
    }

    /**
     * @return mixed
     */
    public function getClase()
    {
        return $this->clase;
    }

    /**
     * @param mixed $clase
     */
    public function setClase($clase): void
    {
        $this->clase = $clase;
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
}
