<?php

namespace App\ClaroEnvios\Mensajerias\BitacoraCotizacion;


use App\Exceptions\ValidacionException;
use Carbon\Carbon;

class BitacoraCotizacionMensajeriaTO
{
    private $id;
    private $mensajeria_id;
    private $comercio_id;
    private $tipo_servicio;
    private $servicio;
    private $costo_mensajeria;
    private $costo_negociacion;
    private $porcentaje_negociacion;
    private $costo_total;
    private $codigo_postal_destino;
    private $codigo_postal_origen;
    private $peso;
    private $largo;
    private $ancho;
    private $alto;
    private $dias_embarque;
    private $fecha_mensajeria_entrega;
    private $fecha_claro_entrega;
    private $moneda;
    private $usuario_id;
    private $bitacoraCotizacionMensajeriaResponseTO;
    private $porcentaje_seguro;
    private $seguro;
    private $valor_paquete;
    private $fecha_cotizacion;
    private $fecha_liberacion;
    public $token;
    private $fecha_inicio;
    private $fecha_fin;
    private $negociacion_id;
    private $tipo_paquete;
    private $costo_adicional;
    private $tiene_cotizacion = true;
    private $numero_externo;
    private $codigo_respuesta;
    private $porcentaje;
    private $paquete_comercio;
    private $tiene_cotizacion_tab;
    private $costo_seguro;
    private $costo_zona_extendida;
    private $envio_internacional;
    private $pais_destino;
    private $id_configuracion;
    private $productos;
    private $peso_volumetrico;
    //variables para multiguia
    private $paquetes;
    private $paquetes_detalle;
    
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

    /**
     * @return mixed
     */
    public function getTipoServicio()
    {
        return $this->tipo_servicio;
    }

    /**
     * @param mixed $tipo_servicio
     */
    public function setTipoServicio($tipo_servicio): void
    {
        $this->tipo_servicio = $tipo_servicio;
    }

    /**
     * @return mixed
     */
    public function getServicio()
    {
        return $this->servicio;
    }

    /**
     * @param mixed $servicio
     */
    public function setServicio($servicio): void
    {
        $this->servicio = $servicio;
    }

    /**
     * @return mixed
     */
    public function getPorcentajeNegociacion()
    {
        return $this->porcentaje_negociacion;
    }

    /**
     * @param mixed $porcentaje
     */
    public function setPorcentajeNegociacion($porcentaje_negociacion): void
    {
        $this->porcentaje_negociacion = $porcentaje_negociacion;
    }

    /**
     * @return mixed
     */
    public function getCostoTotal()
    {
        return $this->costo_total;
    }

    /**
     * @param mixed $costo_porcentaje
     */
    public function setCostoTotal($costo_total): void
    {
        $this->costo_total = $costo_total;
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

    public function setDatosTarificador(\stdClass $servicio)
    {
//        die(print_r($servicio));
        $this->tipo_servicio = $servicio->servicio;
        $this->servicio = $servicio->tipo_servicio;
        $this->costo_mensajeria = $servicio->costo_mensajeria;
        $this->porcentaje_negociacion = $servicio->porcentaje_calculado ?? $servicio->porcentaje_negociacion;
        $this->porcentaje_seguro = $servicio->porcentaje_seguro;
        $this->costo_seguro = $servicio->costo_seguro ?? 0;
        $this->costo_total = $servicio->costo_total;
        $this->peso = $servicio->peso;
        $this->largo = $servicio->largo;
        $this->ancho = $servicio->ancho;
        $this->alto = $servicio->alto;
        $this->fecha_mensajeria_entrega = $servicio->fecha_mensajeria_entrega;
        $this->fecha_claro_entrega = $servicio->fecha_claro_entrega ?? null;
        $this->moneda = $servicio->moneda;
        $this->costo_negociacion = $servicio->costo_negociacion;
        $this->valor_paquete = $servicio->valor_paquete;
        $this->costo_adicional = $servicio->costo_adicional ?? 0;
        $this->costo_zona_extendida = $servicio->costo_zona_extendida ?? 0;
    }

    /**
     * @return mixed
     */
    public function getFechaEntrega()
    {
        return $this->fecha_mensajeria_entrega;
    }

    /**
     * @param mixed $fecha_mensajeria_entrega
     */
    public function setFechaEntrega($fecha_mensajeria_entrega): void
    {
        $this->fecha_mensajeria_entrega = $fecha_mensajeria_entrega;
    }

    /**
     * @return mixed
     */
    public function getFechaEntregaClaro()
    {
        return $this->fecha_claro_entrega;
    }

    /**
     * @param mixed $fecha_claro_entrega
     */
    public function setFechaEntregaClaro($fecha_claro_entrega): void
    {
        $this->fecha_claro_entrega = $fecha_claro_entrega;
    }

    /**
     * @return mixed
     */
    public function getMoneda()
    {
        return $this->moneda;
    }

    /**
     * @param mixed $moneda
     */
    public function setMoneda($moneda): void
    {
        $this->moneda = $moneda;
    }

    public function setDatosBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeria)
    {
        $this->id = $bitacoraCotizacionMensajeria->id;
        $this->mensajeria_id = $bitacoraCotizacionMensajeria->mensajeria_id;
        $this->comercio_id = $bitacoraCotizacionMensajeria->comercio_id;
        $this->tipo_servicio = $bitacoraCotizacionMensajeria->tipo_servicio;
        $this->servicio = $bitacoraCotizacionMensajeria->servicio;
        $this->costo_mensajeria = $bitacoraCotizacionMensajeria->costo;
        $this->costo_total = $bitacoraCotizacionMensajeria->costo_porcentaje;
        $this->porcentaje_negociacion = $bitacoraCotizacionMensajeria->porcentaje;
        $this->moneda = $bitacoraCotizacionMensajeria->moneda;
        $this->codigo_postal_destino = $bitacoraCotizacionMensajeria->codigo_postal_destino;
        $this->codigo_postal_origen = $bitacoraCotizacionMensajeria->codigo_postal_origen;
        $this->peso = $bitacoraCotizacionMensajeria->peso;
        $this->largo = $bitacoraCotizacionMensajeria->largo;
        $this->ancho = $bitacoraCotizacionMensajeria->ancho;
        $this->alto = $bitacoraCotizacionMensajeria->alto;
        $this->dias_embarque = $bitacoraCotizacionMensajeria->dias_embarque;
        $this->fecha_mensajeria_entrega = $bitacoraCotizacionMensajeria->fecha_mensajeria_entrega;
        $this->fecha_claro_entrega = $bitacoraCotizacionMensajeria->fecha_claro_entrega;
        $this->costo_negociacion = $bitacoraCotizacionMensajeria->costo_negociacion;
        $this->porcentaje_seguro = $bitacoraCotizacionMensajeria->porcentaje_seguro;
        $this->seguro = $bitacoraCotizacionMensajeria->seguro;
        $this->fecha_liberacion = new Carbon($bitacoraCotizacionMensajeria->fecha_liberacion);
        $this->tipo_paquete = $bitacoraCotizacionMensajeria->tipo_paquete;
        $this->costo_adicional = $bitacoraCotizacionMensajeria->costo_adiconal;
        $this->negociacion_id = $bitacoraCotizacionMensajeria->negociacion_id;
        $this->usuario_id = $bitacoraCotizacionMensajeria->usuario_id;
        $this->valor_paquete = $bitacoraCotizacionMensajeria->valor_paquete;
        $this->numero_externo = $bitacoraCotizacionMensajeria->numero_externo;
        $this->envio_internacional = $bitacoraCotizacionMensajeria->envio_internacional;
        $this->id_configuracion = $bitacoraCotizacionMensajeria->id_configuracion;
        $this->paquetes = $bitacoraCotizacionMensajeria->paquetes;
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
    public function getBitacoraCotizacionMensajeriaResponseTO()
    {
        return $this->bitacoraCotizacionMensajeriaResponseTO;
    }

    /**
     * @param mixed $bitacoraCotizacionMensajeriaResponseTO
     */
    public function setBitacoraCotizacionMensajeriaResponseTO($bitacoraCotizacionMensajeriaResponseTO): void
    {
        $this->bitacoraCotizacionMensajeriaResponseTO = $bitacoraCotizacionMensajeriaResponseTO;
    }

    /**
     * @return mixed
     */
    public function getCostoNegociacion()
    {
        return $this->costo_negociacion;
    }

    /**
     * @param mixed $costo_convenio
     */
    public function setCostoNegociacion($costo_negociacion): void
    {
        $this->costo_negociacion = $costo_negociacion;
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
    public function getSeguro()
    {
        return $this->seguro;
    }

    /**
     * @param mixed $seguro
     */
    public function setSeguro($seguro): void
    {
        $this->seguro = $seguro;
    }

    /**
     * @return mixed
     */
    public function getValorPaquete()
    {
        return $this->valor_paquete ?? 0;
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
    public function getFechaCotizacion()
    {
        return $this->fecha_cotizacion;
    }

    /**
     * @param mixed $fecha_cotizacion
     */
    public function setFechaCotizacion($fecha_cotizacion): void
    {
        $this->fecha_cotizacion = $fecha_cotizacion;
    }

    /**
     * @return mixed
     */
    public function getFechaLiberacion()
    {
        return $this->fecha_liberacion;
    }

    /**
     * @param mixed $fecha_liberacion
     */
    public function setFechaLiberacion($fecha_liberacion): void
    {
        $this->fecha_liberacion = new Carbon($fecha_liberacion);
    }

    /**
     * @return mixed
     */
    public function getToken(){
        return $this->token;
    }

    /**
     * @param $token
     */
    public function setToken($token){
        $this->token = $token;
    }

    public function setFechaInicio($fecha_inicio): void
    {
        $this->fecha_inicio = $fecha_inicio;
    }

    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    public function setFechaFin($fecha_fin): void
    {
        $this->fecha_fin = $fecha_fin;
    }

    /**
     * @return mixed
     */
    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    /**
     * @return mixed
     */
    public function getCosto()
    {
        return $this->costo_mensajeria;
    }

    /**
     * @param mixed $costo
     */
    public function setCosto($costo): void
    {
        $this->costo_mensajeria = $costo;
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

    /**
     * @return mixed
     */
    public function getCostoAdicional()
    {
        return $this->costo_adicional;
    }

    public function setCostoAdicional($costo_adiconal): void
    {
        $this->costo_adicional = $costo_adiconal;
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
    public function getCostoConvenio()
    {
        return $this->costo_convenio;
    }

    /**
     * @param mixed $costo_convenio
     */
    public function setCostoConvenio($costo_convenio): void
    {
        $this->costo_convenio = $costo_convenio;
    }

    public function setTieneCotizacion($cotizacion){
        $this->tiene_cotizacion = $cotizacion;
    }

    public function getTieneCotizacion(){
        return $this->tiene_cotizacion;
    }

    public function setNumeroExterno($numeroExterno){
        $this->numero_externo = $numeroExterno;
    }

    public function getNumeroExterno(){
        return $this->numero_externo;
    }

    public function setCodigoRespuesta($codigoRespuesta){
        $this->codigo_respuesta = $codigoRespuesta;
    }

    public function getCodigoRespuesta(){
        return $this->codigo_respuesta;
    }

    public function setPaqueteComercio($paqueteComercio){
        $this->paquete_comercio = $paqueteComercio;
    }

    public function getPaqueteComercio(){
        return $this->paquete_comercio;
    }

    public function setTieneCotizacionTab($cotizacionTab){
        $this->tiene_cotizacion_tab = $cotizacionTab;
    }


    public function getTieneCotizacionTab(){
        return $this->tiene_cotizacion_tab;
    }

    public function setCostoSeguro($costoSeguro){
        $this->costo_seguro = $costoSeguro;
    }

    public function getCostoSeguro(){
        return $this->costo_seguro;
    }

    public function setCostoZonaExtendida($costoZonaExtendida){
        $this->costo_zona_extendida = $costoZonaExtendida;
    }

    public function getCostoZonaExtendida(){
        return $this->costo_zona_extendida;
    }

    public function setEnvioInternacional($envioInternacional){
        $this->envio_internacional = $envioInternacional;
    }

    public function getEnvioInternacional(){
        return $this->envio_internacional;
    }

    public function setPaisDestino($paisDestino){
        $this->pais_destino = $paisDestino;
    }

    public function getPaisDestino(){
        return $this->pais_destino;
    }

    public function setIdConfiguracion($idConfiguracion){
        $this->id_configuracion = $idConfiguracion;
    }

    public function getIdConfiguracion(){
        return $this->id_configuracion;
    }

    public function setProductos($productos): void
    {
        $this->productos = $productos;
    }

    public function getProductos()
    {
        return $this->productos;
    }


    public function setPesoVolumetrico($pesoVolumetrico): void
    {
        $this->peso_volumetrico = $pesoVolumetrico;
    }

    public function getPesoVolumetrico()
    {
        return $this->peso_volumetrico;
    }

    public function setPaquetes($paquetes): void
    {
        $this->paquetes = $paquetes;
    }
    //setters y getters para multiguia
    public function getPaquetes()
    {
        return $this->paquetes;
    }

    public function setPaquetesDetalle($paquetes_detalle): void
    {
        $this->paquetes_detalle = $paquetes_detalle;
    }

    public function getPaquetesDetalle()
    {
        return $this->paquetes_detalle;
    }
}
