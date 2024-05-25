<?php

namespace App\ClaroEnvios\Mensajerias;


class GuiaMensajeriaTO
{
    public $id;
    private $guia;
    private $bitacora_cotizacion_mensajeria_id;
    private $bitacora_mensajeria_origen_id;
    private $bitacora_mensajeria_destino_id;
    private $bitacoraCotizacionMensajeriaTO;
    private $bitacoraMensajeriaOrigenTO;
    private $bitacoraMensajeriaDestinoTO;
    private $rutaArchivo;
    private $usuario_id;
    private $guiaMensajeriaDocumentoTO;
    private $status_entrega;
    private $generar_recoleccion;
    private $guiaMensajeriaRecoleccionTO;
    private $fecha_inicio;
    private $fecha_fin;
    private $mensajeria_id;
    private $fecha_status_entrega;
    private $guiaMensajeriaResponseTO;
    private $comercio_id;
    private $contenido;
    private $origen;
    private $notificacion;
    private $numero_externo;
    private $guiaInternacionalTO;
    private $guiaMensajeriaDocumentos;
    private $codificacion = 'utf8';
    private $clave_producto_sat;
    private $cartaPorteTO;
    private $comercio_clave;
    private $descripcion_producto_sat;
    private $tipo_documento;
    private $RFC;

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
    public function getGuia()
    {
        return $this->guia;
    }

    /**
     * @param mixed $guia
     */
    public function setGuia($guia): void
    {
        $this->guia = $guia;
    }

    /**
     * @return mixed
     */
    public function getBitacoraCotizacionMensajeriaId()
    {
        return $this->bitacora_cotizacion_mensajeria_id;
    }

    /**
     * @param mixed $bitacora_cotizacion_mensajeria_id
     */
    public function setBitacoraCotizacionMensajeriaId($bitacora_cotizacion_mensajeria_id): void
    {
        $this->bitacora_cotizacion_mensajeria_id = $bitacora_cotizacion_mensajeria_id;
    }

    /**
     * @return mixed
     */
    public function getBitacoraMensajeriaOrigenId()
    {
        return $this->bitacora_mensajeria_origen_id;
    }

    /**
     * @param mixed $bitacora_mensajeria_origen_id
     */
    public function setBitacoraMensajeriaOrigenId($bitacora_mensajeria_origen_id): void
    {
        $this->bitacora_mensajeria_origen_id = $bitacora_mensajeria_origen_id;
    }

    /**
     * @return mixed
     */
    public function getBitacoraMensajeriaDestinoId()
    {
        return $this->bitacora_mensajeria_destino_id;
    }

    /**
     * @param mixed $bitacora_mensajeria_destino_id
     */
    public function setBitacoraMensajeriaDestinoId($bitacora_mensajeria_destino_id): void
    {
        $this->bitacora_mensajeria_destino_id = $bitacora_mensajeria_destino_id;
    }

    /**
     * @return mixed
     */
    public function getBitacoraCotizacionMensajeriaTO()
    {
        return $this->bitacoraCotizacionMensajeriaTO;
    }

    /**
     * @param mixed $bitacoraCotizacionMensajeriaTO
     */
    public function setBitacoraCotizacionMensajeriaTO($bitacoraCotizacionMensajeriaTO): void
    {
        $this->bitacoraCotizacionMensajeriaTO = $bitacoraCotizacionMensajeriaTO;
    }

    /**
     * @return mixed
     */
    public function getBitacoraMensajeriaOrigenTO()
    {
        return $this->bitacoraMensajeriaOrigenTO;
    }

    /**
     * @param mixed $bitacoraMensajeriaOrigenTO
     */
    public function setBitacoraMensajeriaOrigenTO($bitacoraMensajeriaOrigenTO): void
    {
        $this->bitacoraMensajeriaOrigenTO = $bitacoraMensajeriaOrigenTO;
    }

    /**
     * @return mixed
     */
    public function getBitacoraMensajeriaDestinoTO()
    {
        return $this->bitacoraMensajeriaDestinoTO;
    }

    /**
     * @param mixed $bitacoraMensajeriaDestinoTO
     */
    public function setBitacoraMensajeriaDestinoTO($bitacoraMensajeriaDestinoTO): void
    {
        $this->bitacoraMensajeriaDestinoTO = $bitacoraMensajeriaDestinoTO;
    }

    /**
     * @return mixed
     */
    public function getRutaArchivo()
    {
        return $this->rutaArchivo;
    }

    /**
     * @param mixed $rutaArchivo
     */
    public function setRutaArchivo($rutaArchivo): void
    {
        $this->rutaArchivo = $rutaArchivo;
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
    public function getGuiaMensajeriaDocumentoTO()
    {
        return $this->guiaMensajeriaDocumentoTO;
    }

    /**
     * @param mixed $guiaMensajeriaDocumentoTO
     */
    public function setGuiaMensajeriaDocumentoTO($guiaMensajeriaDocumentoTO): void
    {
        $this->guiaMensajeriaDocumentoTO = $guiaMensajeriaDocumentoTO;
    }

    public function setGuiaMensajeriaDocumentos(array $guiaMensajeriaDocumentos): void
    {
        $this->guiaMensajeriaDocumentos = $guiaMensajeriaDocumentos;
    }

    public function getGuiaMensajeriaDocumentos()
    {
        return $this->guiaMensajeriaDocumentos;
    }

    /**
     * @return mixed
     */
    public function getStatusEntrega()
    {
        return $this->status_entrega;
    }

    /**
     * @param mixed $status_entrega
     */
    public function setStatusEntrega($status_entrega): void
    {
        $this->status_entrega = $status_entrega;
    }

    /**
     * @return mixed
     */
    public function getGenerarRecoleccion()
    {
        return $this->generar_recoleccion;
    }

    /**
     * @param mixed $generar_recoleccion
     */
    public function setGenerarRecoleccion($generar_recoleccion): void
    {
        $this->generar_recoleccion = $generar_recoleccion;
    }

    /**
     * @return mixed
     */
    public function getGuiaMensajeriaRecoleccionTO()
    {
        return $this->guiaMensajeriaRecoleccionTO;
    }

    /**
     * @param mixed $guiaMensajeriaRecoleccionTO
     */
    public function setGuiaMensajeriaRecoleccionTO($guiaMensajeriaRecoleccionTO): void
    {
        $this->guiaMensajeriaRecoleccionTO = $guiaMensajeriaRecoleccionTO;
    }

    /**
     * @return mixed
     */
    public function getFechaInicio()
    {
        return $this->fecha_inicio;
    }

    /**
     * @param mixed $fecha_inicio
     */
    public function setFechaInicio($fecha_inicio): void
    {
        $this->fecha_inicio = $fecha_inicio;
    }

    /**
     * @return mixed
     */
    public function getFechaFin()
    {
        return $this->fecha_fin;
    }

    /**
     * @param mixed $fecha_fin
     */
    public function setFechaFin($fecha_fin): void
    {
        $this->fecha_fin = $fecha_fin;
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
     * @param $comercio_id
     */
    public function setComercioId($comercio_id)
    {
        $this->comercio_id = $comercio_id;
    }

    /**
     * @return mixed
     */
    public function getFechaStatusEntrega()
    {
        return $this->fecha_status_entrega;
    }

    /**
     * @param mixed $fecha_status_entrega
     */
    public function setFechaStatusEntrega($fecha_status_entrega): void
    {
        $this->fecha_status_entrega = $fecha_status_entrega;
    }

    /**
     * @return mixed
     */
    public function getGuiaMensajeriaResponseTO()
    {
        return $this->guiaMensajeriaResponseTO;
    }

    /**
     * @param mixed $guiaMensajeriaResponseTO
     */
    public function setGuiaMensajeriaResponseTO($guiaMensajeriaResponseTO): void
    {
        $this->guiaMensajeriaResponseTO = $guiaMensajeriaResponseTO;
    }

    /**
     * @return mixed
     */
    public function getContenido()
    {
        return $this->contenido;
    }

    /**
     * @param $contenido
     */
    public function setContenido($contenido)
    {
        $this->contenido = $contenido;
    }

    public function getOrigen()
    {
        return $this->origen;
    }

    public function setOrigen($origen)
    {
        $this->origen = $origen;
    }

    public function getNotificacion()
    {
        return $this->notificacion;
    }

    public function setNotificacion($notificacion)
    {
        $this->notificacion = $notificacion;
    }

    public function getNumeroExterno()
    {
        return $this->numero_externo;
    }

    public function setNumeroExterno($numeroExterno)
    {
        $this->numero_externo = $numeroExterno;
    }


    public function setGuiaInternacionalTO($GuiaInternacionalTO): void
    {
        $this->guiaInternacionalTO = $GuiaInternacionalTO;
    }

    public function getGuiaInternacionalTO()
    {
        return $this->guiaInternacionalTO;
    }

    public function setCodificacion($codificacion): void
    {
        $this->codificacion = $codificacion;
    }
    public function getCodificacion()
    {
        return $this->codificacion;
    }

    public function setClaveProductoSAT($claveProductoSAT){
        $this->clave_producto_sat = $claveProductoSAT;
    }

    public function getClaveProductoSAT(){
        return $this->clave_producto_sat;
    }

    public function setCartaPorteTO($cartaPorteTO): void
    {
        $this->cartaPorteTO = $cartaPorteTO;
    }

    public function getCartaPorteTO()
    {
        return $this->cartaPorteTO;
    }
    
    public function setComercioClave($clave){
        $this->comercio_clave = $clave;
    }

    public function getComercioClave(){
        return $this->comercio_clave;
    }

    public function setDescripcionProductoSat($descripcionProductoSat){
        $this->descripcion_producto_sat = $descripcionProductoSat;
    }

    public function getDescripcionProductoSat(){
        return $this->descripcion_producto_sat;
    }

    public function getTipoDocumento()
    {
        return $this->tipo_documento;
    }

    public function setTipoDocumento($tipoDocumento)
    {
        $this->tipo_documento = $tipoDocumento;
    }

    public function setRFC($rfc): void
    {
        $this->RFC= $rfc;
    }

    public function getRFC()
    {
        return $this->RFC;
    }


}
