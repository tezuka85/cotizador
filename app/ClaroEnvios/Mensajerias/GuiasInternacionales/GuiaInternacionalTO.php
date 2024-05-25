<?php

namespace App\ClaroEnvios\Mensajerias\GuiasInternacionales;


class GuiaInternacionalTO
{
    private $id;
    public $id_guia_mensajeria;
    private $pais_destino;
    private $proposito_envio;
    private $categoria;
    private $pais_fabricacion;
    private $total_envios;
    private $moneda;
    private $id_bitacora_cotizacion;

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
    public function getIdGuiaMensajeria()
    {
        return $this->id_guia_mensajeria;
    }

    /**
     * @param mixed $guia_mensajeria_id
     */
    public function setIdGuiaMensajeria($idGuiaMensajeria): void
    {
        $this->id_guia_mensajeria= $idGuiaMensajeria;
    }

    /**
     * @return mixed
     */
    public function getPaisDestino()
    {
        return $this->pais_destino;
    }

    /**
     * @param mixed $request
     */
    public function setPaisDestino($paisDestino): void
    {
        $this->pais_destino = $paisDestino;
    }

    /**
     * @return mixed
     */
    public function getPropositoEnvio()
    {
        return $this->proposito_envio;
    }

    /**
     * @param mixed $response
     */
    public function setPropositoEnvio($propositoEnvio): void
    {
        $this->proposito_envio = $propositoEnvio;
    }

    /**
     * @return mixed
     */
    public function getPaisFabricacion()
    {
        return $this->pais_fabricacion;
    }

    public function setPaisFabricacion($paisFabricacion): void
    {
        $this->pais_fabricacion = $paisFabricacion;
    }

    public function getTotalEnvios()
    {
        return $this->total_envios;
    }

    public function setTotalEnvios($totalEnvios): void
    {
        $this->total_envios = $totalEnvios;
    }

    public function getMoneda()
    {
        return $this->moneda;
    }

    public function setMoneda($moneda): void
    {
        $this->moneda = $moneda;
    }

    public function getIdBitacoraCotizacion()
    {
        return $this->id_bitacora_cotizacion;
    }

    public function setIdBitacoraCotizacion($bitacoraCotizacion): void
    {
        $this->id_bitacora_cotizacion = $bitacoraCotizacion;
    }

}