<?php

namespace App\ClaroEnvios\Mensajerias\CartaPorte;


class CartaPorteT0
{
    private $productos;

    /**
     * @return mixed
     */
    public function getProductos()
    {
        return $this->productos;
    }

    /**
     * @param mixed $id
     */
    public function setProductos($productos): void
    {
        $this->productos= $productos;
    }
}