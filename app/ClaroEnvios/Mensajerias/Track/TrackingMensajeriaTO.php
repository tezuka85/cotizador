<?php

namespace App\ClaroEnvios\Mensajerias\Track;


class TrackingMensajeriaTO
{
    private $id;
    private $num_orden;
    private $id_seller;
    private $id_mensajeria;
    private $guia;
    private $fecha_hora;
    private $fecha_inicio;
    private $fecha_fin;
    private $codigo;
    private $familia_externa;
    private $pedido_comercio;
    private $limit;
    private $page;
    private $ids_sellers;

    private $order = 'desc';
    private $columna = 'fecha_hora';
    private $guias;



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
    public function getNumOrden()
    {
        return intval($this->num_orden);
    }

    /**
     * @param mixed $num_orden
     */
    public function setNumOrden($numOrden): void
    {
        $this->num_orden = $numOrden;
    }

    /**
     * @return mixed
     */
    public function getIdSeller()
    {
        return $this->id_seller;
    }

    /**
     * @param mixed $request
     */
    public function setIdSeller($idSeller): void
    {
        $this->id_seller = $idSeller;
    }

    /**
     * @return mixed
     */
    public function getIdMensajeria()
    {
        return $this->id_mensajeria;
    }

    /**
     * @param mixed $response
     */
    public function setIdMensajeria($idMensajeria): void
    {
        $this->id_mensajeria = $idMensajeria;
    }

    /**
     * @return mixed
     */
    public function getGuia()
    {
        return $this->guia;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setGuia($guia): void
    {
        $this->guia = $guia;
    }

    /**
     * @return mixed
     */
    public function getFechaHora()
    {
        return $this->fecha_hora;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setFechaHora($fechaHora): void
    {
        $this->fecha_hora = $fechaHora;
    }

    public function getCodigo()
    {
        return $this->codigo;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setCodigo($codigo): void
    {
        $this->codigo = $codigo;
    }

    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setFechaInicio($fechaInicio): void
    {
        $this->fecha_inicio = $fechaInicio;
    }

    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setFechaFin($fechaFin): void
    {
        $this->fecha_fin = $fechaFin;
    }

    public function getLimit()
    {
        return intval($this->limit);
    }

    /**
     * @param mixed $usuario_id
     */
    public function setLimit($limit): void
    {
        $this->limit = $limit;
    }

    public function getPage()
    {
       return intval($this->page) - 1;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setPage($page): void
    {
        $this->page = $page;
    }

    public function getOrder()
    {
        return $this->order;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setOrder($order): void
    {
        $this->order = $order;
    }

    public function getColumna()
    {
        return $this->columna;
    }

    /**
     * @param mixed $usuario_id
     */
    public function setColumna($columna): void
    {
        $this->columna = $columna;
    }


    public function getFamiliaExterna()
    {
        return $this->familia_externa;
    }


    public function setFamiliaExterna($familiaExterna): void
    {
        $this->familia_externa = $familiaExterna;
    }

    public function getPedidcComercio()
    {
        return $this->pedido_comercio;
    }

    public function setPedidoComercio($pedidoComercio): void
    {
        $this->pedido_comercio = $pedidoComercio;
    }

    public function getGuias()
    {
        return $this->guias;
    }

    public function setGuias($guias): void
    {
        $this->guias = $guias;
    }

    public function getIdsSellers()
    {
        return $this->ids_sellers;
    }

    public function setIdsSellers($idsSellers): void
    {
        $this->ids_sellers = $idsSellers;
    }
}