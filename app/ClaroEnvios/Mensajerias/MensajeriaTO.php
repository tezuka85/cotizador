<?php

namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Sepomex\Sepomex;
use Carbon\Carbon;

class MensajeriaTO
{
    private $codigo_postal_origen   ;
    private $codigo_postal_destino;
    private $peso;
    private $largo;
    private $ancho;
    private $alto;
    private $embarque;
    private $comercio;
    private $porcentaje;
    private $dias_embarque;
    private $siglas_codigo_origen;
    private $siglas_codigo_destino;
    private $id;
    private $costo;
    private $porcentaje_seguro = 0;
    private $valor_paquete;
    private $formato_guia_impresion;
    private $extension_guia_impresion;
    private $fecha_liberacion;
    private $negociacion_id;
    private $tipo_paquete;
    private $costo_adicional;
    private $tabulador;
    private $seguro;
    private $equipo;
    private $pedido;
    private $tipo;
    private $tienda_id;
    private $tienda_nombre;
    private $custom;
    private $paquete_comercio;
    private $coto_seguro;
    private $costo_zona_extendida;
    private $numero_externo;
    private $codigo_estado;
    private $pais_destino;
    private $pais_fabricacion;
    private $categoria;
    private $moneda = 'MXN';
    private $peso_calculado;
    private $negociacion;
    private $id_servicio;
    private $piezas;
    private $id_configuracion;
    private $paquetes;
    private $paquetes_detalle;

    /**
     * MensajeriaTO constructor.
     */
    public function __construct($arrayMensajeriaDatos = [])
    {
        foreach ($arrayMensajeriaDatos as $key=>$value) {
            if (property_exists($this, $key)) {
                $this->{$key} = $value;
            }
        }

        $fechaLiberacion = new Carbon();
        $this->fecha_liberacion = $fechaLiberacion;
        if (!is_null($this->dias_embarque) && $this->dias_embarque != 0) {
            $fechaLiberacion->addDays($this->dias_embarque);
            $this->fecha_liberacion = $fechaLiberacion;
        }
    }

    /**
     * @return mixed
     */
    public function getCodigoPostalOrigen()
    {
        return $this->codigo_postal_origen;
    }

    /**
     * @param mixed $codigo_postal_origen
     */
    public function setCodigoPostalOrigen($codigo_postal_origen): void
    {
        $this->codigo_postal_origen = $codigo_postal_origen;
    }

    /**
     * @return mixed
     */
    public function getCodigoPostalDestino()
    {
        return $this->codigo_postal_destino;
    }

    /**
     * @param mixed $codigo_postal_destino
     */
    public function setCodigoPostalDestino($codigo_postal_destino): void
    {
        $this->codigo_postal_destino = $codigo_postal_destino;
    }

    /**
     * @return mixed
     */
    public function getPeso()
    {
        return $this->peso;
    }

    /**
     * @param mixed $peso
     */
    public function setPeso($peso): void
    {
        $this->peso = $peso;
    }

    /**
     * @return mixed
     */
    public function getLargo()
    {
        return $this->largo;
    }

    /**
     * @param mixed $largo
     */
    public function setLargo($largo): void
    {
        $this->largo = $largo;
    }

    /**
     * @return mixed
     */
    public function getAncho()
    {
        return $this->ancho;
    }

    /**
     * @param mixed $ancho
     */
    public function setAncho($ancho): void
    {
        $this->ancho = $ancho;
    }

    /**
     * @return mixed
     */
    public function getAlto()
    {
        return $this->alto;
    }

    /**
     * @param mixed $alto
     */
    public function setAlto($alto): void
    {
        $this->alto = $alto;
    }

    /**
     * @return mixed
     */
    public function getEmbarque()
    {
        return $this->embarque;
    }

    /**
     * @param mixed $embarque
     */
    public function setEmbarque($embarque): void
    {
        $this->embarque = $embarque;
    }

    /**
     * @return mixed
     */
    public function getComercio()
    {
        return $this->comercio;
    }

    /**
     * @param mixed $comercio
     */
    public function setComercio($comercio): void
    {
        $this->comercio = $comercio;
    }

    /**
     * @return mixed
     */
    public function getPorcentaje()
    {
        return $this->porcentaje;
    }

    /**
     * @param mixed $porcentaje
     */
    public function setPorcentaje($porcentaje): void
    {
        $this->porcentaje = $porcentaje;
    }

    /**
     * @return mixed
     */
    public function getDiasEmbarque()
    {
        return $this->dias_embarque;
    }

    /**
     * @param mixed $dias_embarque
     */
    public function setDiasEmbarque($dias_embarque): void
    {
        $this->dias_embarque = $dias_embarque;
    }

    /**
     * @return mixed
     */
    public function getSiglasCodigoOrigen()
    {
        return $this->siglas_codigo_origen;
    }

    /**
     * @param mixed $siglas_codigo_origen
     */
    public function setSiglasCodigoOrigen($siglas_codigo_origen): void
    {
        $this->siglas_codigo_origen = $siglas_codigo_origen;
    }

    /**
     * @return mixed
     */
    public function getSiglasCodigoDestino()
    {
        return $this->siglas_codigo_destino;
    }

    /**
     * @param mixed $siglas_codigo_destino
     */
    public function setSiglasCodigoDestino($siglas_codigo_destino): void
    {
        $this->siglas_codigo_destino = $siglas_codigo_destino;
    }

    public function buscarSiglasSepomex()
    {
        $sepomex = new Sepomex();
        $this->siglas_codigo_origen = $sepomex
            ->obtenerSiglasEDO($this->codigo_postal_origen) ?$sepomex->obtenerSiglasEDO($this->codigo_postal_origen)->sigla : '';
        $this->siglas_codigo_destino = $sepomex
            ->obtenerSiglasEDO($this->codigo_postal_destino) ? $sepomex->obtenerSiglasEDO($this->codigo_postal_destino)->sigla : '';
//        die(print_r( $sepomex->obtenerSiglasEDO($this->codigo_postal_destino)));

    }

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
    public function getCosto()
    {
        return $this->costo;
    }

    /**
     * @param mixed $costo
     */
    public function setCosto($costo): void
    {
        $this->costo = $costo;
    }

    /**
     * @return mixed
     */
    public function getPorcentajeSeguro()
    {
        return $this->porcentaje_seguro;
    }

    /**
     * @param mixed $porcentaje_seguro
     */
    public function setPorcentajeSeguro($porcentaje_seguro): void
    {
        $this->porcentaje_seguro = $porcentaje_seguro;
    }

    /**
     * @return mixed
     */
    public function getValorPaquete()
    {
        return $this->valor_paquete;
    }

    /**
     * @param mixed $valor_paquete
     */
    public function setValorPaquete($valor_paquete): void
    {
        $this->valor_paquete = $valor_paquete;
    }

    /**
     * @return mixed
     */
    public function getFormatoGuiaImpresion()
    {
        return $this->formato_guia_impresion;
    }

    /**
     * @param mixed $formato_guia_impresion
     */
    public function setFormatoGuiaImpresion($formato_guia_impresion): void
    {
        $this->formato_guia_impresion = $formato_guia_impresion;
    }

    /**
     * @return mixed
     */
    public function getExtensionGuiaImpresion()
    {
        return $this->extension_guia_impresion;
    }

    /**
     * @param mixed $extension_guia_impresion
     */
    public function setExtensionGuiaImpresion($extension_guia_impresion): void
    {
        $this->extension_guia_impresion = $extension_guia_impresion;
    }

    /**
     * @return Carbon
     */
    public function getFechaLiberacion()
    {
        return $this->fecha_liberacion;
    }

    /**
     * @param Carbon $fecha_liberacion
     */
    public function setFechaLiberacion(Carbon $fecha_liberacion): void
    {
        $this->fecha_liberacion = $fecha_liberacion;
    }

    public function getNegociacionId()
    {
        return $this->negociacion_id;
    }

    public function setNegociacionId($negociacion_id)
    {
        $this->negociacion_id = $negociacion_id;
    }

    /**
     * @return mixed
     */
    public function getTipoPaquete()
    {
        return $this->tipo_paquete;
    }

    /**
     * @param mixed $tipo_paquete
     */
    public function setTipoPaquete($tipo_paquete): void
    {
        $this->tipo_paquete = $tipo_paquete;
    }


    public function getCostoAdicional()
    {
        return $this->costo_adicional;
    }

    public function setCostoAdicional($costoAdicional)
    {
        $this->costo_adicional = $costoAdicional;
    }

    public function getTabulador()
    {
        return $this->tabulador;
    }

    public function setTabulador($tabulador)
    {
        $this->tabulador = $tabulador;
    }

    public function getSeguro()
    {
        return $this->seguro;
    }

    public function setSeguro($seguro)
    {
        $this->seguro = $seguro;
    }

    public function getEquipo()
    {
        return $this->equipo;
    }

    public function setEquipo($equipo)
    {
        $this->equipo = $equipo;
    }

    public function getPedido()
    {
        return $this->pedido;
    }

    public function setPedido($pedido)
    {
        $this->pedido = $pedido;
    }

    public function getTipo()
    {
        return $this->tipo;
    }

    public function setTipo($tipo)
    {
        $this->tipo = $tipo;
    }

    public function getTiendaId()
    {
        return $this->tienda_id;
    }

    public function setTiendaId($tiendaId)
    {
        $this->tienda_id = $tiendaId;
    }

    public function getTiendaNombre()
    {
        return $this->tienda_nombre;
    }

    public function setTiendaNombre($tiendaNombre)
    {
        $this->tienda_nombre = $tiendaNombre;
    }
    public function getCustom()
    {
        return $this->custom;
    }

    public function setCustom($custom)
    {
        $this->custom = $custom;
    }

    public function setPaqueteComercio($paqueteComercio){
        $this->paquete_comercio = $paqueteComercio;
    }

    public function getPaqueteComercio(){
        return $this->paquete_comercio;
    }

    public function setCostoSeguro($costoSeguro){
        $this->coto_seguro = $costoSeguro;
    }

    public function getCostoSeguro(){
        return $this->coto_seguro;
    }

    public function setCostoZonaExtendida($costoZonaExtendida){
        $this->costo_zona_extendida = $costoZonaExtendida;
    }

    public function getCostoZonaExtendida(){
        return $this->costo_zona_extendida;
    }

    public function setNumeroExterno($numeroExterno){
        $this->numero_externo = $numeroExterno;
    }

    public function getNumeroExterno(){
        return $this->numero_externo;
    }

    public function setCodigoEstado($codigoEstado){
        $this->codigo_estado = $codigoEstado;
    }

    public function getCodigoEstado(){
        return $this->codigo_estado;
    }

    public function setPaisDestino($paisDestino){
        $this->pais_destino = $paisDestino;
    }

    public function getPaisDestino(){
        return $this->pais_destino;
    }

    public function setPaisFabricacion($paisFabricacion){
        $this->pais_fabricacion = $paisFabricacion;
    }

    public function getPaisFabricacion(){
        return $this->pais_fabricacion;
    }

    public function setCategoria($categoria){
        $this->categoria = $categoria;
    }

    public function getCategoria(){
        return $this->categoria;
    }

    public function setMoneda($moneda){
        $this->moneda = $moneda;
    }

    public function getMoneda(){
        return $this->moneda;
    }

    public function setPesoCalculado($pesoCalculado){
        $this->peso_calculado = $pesoCalculado;
    }

    public function getPesoCalculado(){
        return $this->peso_calculado;
    }

    public function setNegociacion($negociacion){
        $this->negociacion = $negociacion;
    }

    public function getNegociacion(){
        return $this->negociacion;
    }

    public function setIdServicio($idServicio){
        $this->id_servicio = $idServicio;
    }

    public function getIdServicio(){
        return $this->id_servicio;
    }

    //crea aun get de piezas
    public function getPiezas()
    {
        return $this->piezas;
    }

    //crea un set de piezas
    public function setPiezas($piezas)
    {
        $this->piezas = $piezas;
    }

    public function getIdConfiguracion()
    {
        return $this->id_configuracion;
    }


    public function setIdConfiguracion($idConfiguracion)
    {
        $this->id_configuracion = $idConfiguracion;
    }

    //Getter y Setter para Paquetes multiguia
    public function getPaquetes()
    {
        return $this->paquetes;
    }

    public function setPaquetes($paquetes)
    {
        $this->paquetes = $paquetes;
    }

    public function getPaquetesDetalle()
    {
        return $this->paquetes_detalle;
    }

    public function setPaquetesDetalle($paquetes_detalle)
    {
        $this->paquetes_detalle = $paquetes_detalle;
    }
}
