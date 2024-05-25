<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\Comercios\CamposLimitesMensajerias\CampoLimiteMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestino;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigen;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaResponse;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaResponseTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\CotizacionPaquete;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\PaqueteCotizacion;
use App\ClaroEnvios\Mensajerias\Configuracion\ConfiguracionMensajeriaUsuario;
use App\ClaroEnvios\Mensajerias\Configuracion\ConfiguracionMensajeriaUsuarioTO;
use App\ClaroEnvios\Mensajerias\FormatoGuiaImpresionMensajeria\FormatoGuiaImpresionMensajeria;
use App\ClaroEnvios\Mensajerias\FormatoGuiaImpresionMensajeria\FormatoGuiaImpresionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Guias\GuiaMensajeriaResponse;
use App\ClaroEnvios\Mensajerias\Guias\GuiaMensajeriaResponseTO;
use App\ClaroEnvios\Mensajerias\GuiasInternacionales\GuiaInternacional;
use App\ClaroEnvios\Mensajerias\GuiasInternacionales\GuiaInternacionalTO;
use App\ClaroEnvios\Mensajerias\ProductoCotizacion\ProductoCotizacion;
use App\ClaroEnvios\Mensajerias\Recoleccion\GuiaMensajeriaRecoleccion;
use App\ClaroEnvios\Mensajerias\Recoleccion\GuiaMensajeriaRecoleccionResponse;
use App\ClaroEnvios\Mensajerias\Recoleccion\GuiaMensajeriaRecoleccionResponseTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\GuiaMensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccion;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Track\TrackingMensajeria;
use App\ClaroEnvios\Mensajerias\Track\TrackingMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponseTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponse;
use App\ClaroEnvios\Saldos\SGeneral;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaRepository
 * @package App\ClaroEnvios\Mensajerias
 */
class MensajeriaRepository implements MensajeriaRepositoryInterface
{

    public function __construct() {
        @ini_set( 'memory_limit', '-1');
        @ini_set( 'max_execution_time', '-1' );

    }


    /**
     * Metodo que busca las mensajerias en la base de datos de acuerdo a los parametros pasados
     * @param MensajeriaTO $mensajeriaTO
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function buscarMensajerias(MensajeriaTO $mensajeriaTO)
    {
        return Mensajeria::query()->get();
    }

    /**
     * Busca los costos de mensajerias porcentajes de acuerdo a los parametros pasados en el TO
     * y por el arreglo de mensajeria_id como parametro opcional
     * @param CostoMensajeriaTO $costoMensajeriaTO
     * @param array $arrayMensajeriasIds
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function buscarCostosMensajeriasPorcentajes(
        CostoMensajeriaTO $costoMensajeriaTO,
        $arrayMensajeriasIds = []
    )
    {
        $costosMensajeriasPorcentajes = CostoMensajeria::query();
        $costosMensajeriasPorcentajes->when(
            count($arrayMensajeriasIds),
            function ($query) use ($arrayMensajeriasIds) {
                $query->whereIn('mensajeria_id', $arrayMensajeriasIds);
            }
        );
        $costosMensajeriasPorcentajes->when(
            $costoMensajeriaTO->getComercioId(),
            function ($query) use ($costoMensajeriaTO) {
                $query->where('comercio_id', $costoMensajeriaTO->getComercioId());
            }
        );
        return $costosMensajeriasPorcentajes = $costosMensajeriasPorcentajes->get();
    }

    /**
     * Metodo que guarda la bitacoraCotizacionMensajeria a partir de la respuesta de la cotizacion
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed|void
     * @throws \Exception
     */
    public function guardarBitacoraCotizacionMensajeria(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
        DB::transaction(
            function () use ($bitacoraCotizacionMensajeriaTO) {
                $bitacoraCotizacionMensajeriaResponseTO = $bitacoraCotizacionMensajeriaTO
                    ->getBitacoraCotizacionMensajeriaResponseTO();
                $bitacoraCotizacionMensajeria = new BitacoraCotizacionMensajeria();
                $bitacoraCotizacionMensajeria->mensajeria_id = $bitacoraCotizacionMensajeriaTO->getMensajeriaId();
                $bitacoraCotizacionMensajeria->comercio_id = $bitacoraCotizacionMensajeriaTO->getComercioId();
                $bitacoraCotizacionMensajeria->tipo_servicio = $bitacoraCotizacionMensajeriaTO->getTipoServicio();
                $bitacoraCotizacionMensajeria->servicio = $bitacoraCotizacionMensajeriaTO->getServicio();
                $bitacoraCotizacionMensajeria->costo = $bitacoraCotizacionMensajeriaTO->getCosto();
                $bitacoraCotizacionMensajeria->porcentaje = $bitacoraCotizacionMensajeriaTO->getPorcentajeNegociacion();
                $bitacoraCotizacionMensajeria->porcentaje_seguro = $bitacoraCotizacionMensajeriaTO->getPorcentajeSeguro();
                $bitacoraCotizacionMensajeria->costo_convenio = $bitacoraCotizacionMensajeriaTO->getCostoNegociacion();
                $bitacoraCotizacionMensajeria->costo_porcentaje = $bitacoraCotizacionMensajeriaTO->getCostoTotal();
                $bitacoraCotizacionMensajeria->moneda = $bitacoraCotizacionMensajeriaTO->getMoneda();
                $bitacoraCotizacionMensajeria->codigo_postal_origen = $bitacoraCotizacionMensajeriaTO->getCodigoPostalOrigen();
                $bitacoraCotizacionMensajeria->codigo_postal_destino = $bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino();
                $bitacoraCotizacionMensajeria->peso = $bitacoraCotizacionMensajeriaTO->getPeso();
                $bitacoraCotizacionMensajeria->largo = $bitacoraCotizacionMensajeriaTO->getLargo();
                $bitacoraCotizacionMensajeria->ancho = $bitacoraCotizacionMensajeriaTO->getAncho();
                $bitacoraCotizacionMensajeria->alto = $bitacoraCotizacionMensajeriaTO->getAlto();
                $bitacoraCotizacionMensajeria->dias_embarque = $bitacoraCotizacionMensajeriaTO->getDiasEmbarque();
                $bitacoraCotizacionMensajeria->fecha_cotizacion = date('Y-m-d H:i:s');
                $bitacoraCotizacionMensajeria->fecha_mensajeria_entrega = $bitacoraCotizacionMensajeriaTO->getFechaEntrega();
                $bitacoraCotizacionMensajeria->fecha_claro_entrega = $bitacoraCotizacionMensajeriaTO->getFechaEntregaClaro();
                $bitacoraCotizacionMensajeria->seguro = $bitacoraCotizacionMensajeriaTO->getSeguro();
                $bitacoraCotizacionMensajeria->usuario_id = $bitacoraCotizacionMensajeriaTO->getUsuarioId();
                $bitacoraCotizacionMensajeria->valor_paquete = $bitacoraCotizacionMensajeriaTO->getValorPaquete();
                $bitacoraCotizacionMensajeria->fecha_liberacion = $bitacoraCotizacionMensajeriaTO->getFechaLiberacion();
                $bitacoraCotizacionMensajeria->negociacion_id = $bitacoraCotizacionMensajeriaTO->getNegociacionId();
                $bitacoraCotizacionMensajeria->tipo_paquete = $bitacoraCotizacionMensajeriaTO->getTipoPaquete();
                $bitacoraCotizacionMensajeria->costo_adicional = $bitacoraCotizacionMensajeriaTO->getCostoAdicional();
                $bitacoraCotizacionMensajeria->costo_seguro = $bitacoraCotizacionMensajeriaTO->getCostoSeguro();
                $bitacoraCotizacionMensajeria->costo_zona_extendida = $bitacoraCotizacionMensajeriaTO->getCostoZonaExtendida() ?? 0;
                $bitacoraCotizacionMensajeria->numero_externo = $bitacoraCotizacionMensajeriaTO->getNumeroExterno();
                $bitacoraCotizacionMensajeria->envio_internacional = $bitacoraCotizacionMensajeriaTO->getEnvioInternacional()?? 0;
                $bitacoraCotizacionMensajeria->id_configuracion = $bitacoraCotizacionMensajeriaTO->getIdConfiguracion() ?? 1;
                $bitacoraCotizacionMensajeria->peso_volumetrico = $bitacoraCotizacionMensajeriaTO->getPesoVolumetrico();
                $bitacoraCotizacionMensajeria->paquetes = $bitacoraCotizacionMensajeriaTO->getPaquetes();
//                $bitacoraCotizacionMensajeria->pais_destino = $bitacoraCotizacionMensajeriaTO->getPaisDestino()?? '';
//                $bitacoraCotizacionMensajeria->moneda = $bitacoraCotizacionMensajeriaTO->getMoneda();
                $bitacoraCotizacionMensajeria->setToken();
                
               
                $bitacoraCotizacionMensajeria->save();
                $bitacoraCotizacionMensajeriaTO->setId($bitacoraCotizacionMensajeria->id);
                $bitacoraCotizacionMensajeriaTO->setToken($bitacoraCotizacionMensajeria->token);

                //Guarda response cuando existe consumo de api de cotizacion de mensajeria(negociacion no es por tabulador)
                $configuracionesZonas = [3,7,8];
                if ($bitacoraCotizacionMensajeriaTO->getTieneCotizacion() && 
                in_array($bitacoraCotizacionMensajeria->id_configuracion, $configuracionesZonas)) {
                    $bitacoraCotizacionMensajeriaResponseTO->setBitacoraCotizacionMensajeriaId($bitacoraCotizacionMensajeria->id);
                    $this->guardarBitacoraCotizacionMensajeriaResponse($bitacoraCotizacionMensajeriaResponseTO);
                } elseif (!$bitacoraCotizacionMensajeriaTO->getTieneCotizacion() && $bitacoraCotizacionMensajeriaTO->getTieneCotizacionTab() == true &&
                !in_array($bitacoraCotizacionMensajeria->id_configuracion, $configuracionesZonas)) {
                    //Guarda paquete y estatus de cotizacion para t1paginas

                   // die(print_r($bitacoraCotizacionMensajeria));
                    $cotizacionPaquete = new CotizacionPaquete();
                    $cotizacionPaquete->id_bitacora_cotizacion = $bitacoraCotizacionMensajeria->id;
                    $cotizacionPaquete->id_paquete = $bitacoraCotizacionMensajeriaTO->getPaqueteComercio();
                    $cotizacionPaquete->save();
                }

                //die(print_r($bitacoraCotizacionMensajeriaTO));
                //Guarda productos de fedex para mandarlos en carta porte
                //se agrega id 22 de exprees de dev revisar id de prod para agregar y quitar el 22 una ves se pruebe todo 
                /*if(in_array($bitacoraCotizacionMensajeriaTO->getMensajeriaId(),[2,18,20,22]) && $bitacoraCotizacionMensajeriaTO->getProductos()){
                    Log::info("Cotizacion fedex guarda productos");
                    $this->guardarProductoCotizacion($bitacoraCotizacionMensajeriaTO);

                }*/

                if (env('API_LOCATION') === 'release' || env('API_LOCATION') === 'production') {
                    if (in_array($bitacoraCotizacionMensajeriaTO->getMensajeriaId(), [2, 18, 20]) && $bitacoraCotizacionMensajeriaTO->getProductos()) {
                        Log::info("Cotizacion ambiente ".env('API_LOCATION'));
                        $this->guardarProductoCotizacion($bitacoraCotizacionMensajeriaTO);
                    }
                } else {
                    if (in_array($bitacoraCotizacionMensajeriaTO->getMensajeriaId(), [2, 18, 22]) && $bitacoraCotizacionMensajeriaTO->getProductos()) {
                        Log::info("Cotizacion ambiente ".env('API_LOCATION'));
                        $this->guardarProductoCotizacion($bitacoraCotizacionMensajeriaTO);
                    }
                }

                if ($bitacoraCotizacionMensajeriaTO->getPaquetesDetalle()!=null) {
                    Log::info("Tiene paquetes detalle");
                    $this->guardarPaquetesCotizacion($bitacoraCotizacionMensajeriaTO);
                }
                
                    
            });
    }

    /**
     * Metodo que guarda la guia de la mensajeria junto con sus tablas anidadas
     * como son los origenes y destinos
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed|void
     */
    public function guardarGuiMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null)
    {
//        die(print_r($guiaMensajeriaTO->getGuiaInternacionalTO()));
        DB::transaction(
            function () use ($guiaMensajeriaTO,$pgs) {
                $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
                $bitacoraMensajeriaDestinoTO = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
                $guiaMensajeriaDocumentoTO = $guiaMensajeriaTO->getGuiaMensajeriaDocumentoTO();
                $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
                $this->guardarBitacoraMensajeriaOrigen($bitacoraMensajeriaOrigenTO);
                $this->guardarBitacoraMensajeriaDestino($bitacoraMensajeriaDestinoTO);
                $guiaMensajeria = new GuiaMensajeria();
                $guiaMensajeria->guia = $guiaMensajeriaTO->getGuia();
                $guiaMensajeria->bitacora_cotizacion_mensajeria_id = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId();
                $guiaMensajeria->bitacora_mensajeria_origen_id = $bitacoraMensajeriaOrigenTO->getId();
                $guiaMensajeria->bitacora_mensajeria_destino_id = $bitacoraMensajeriaDestinoTO->getId();
                $guiaMensajeria->usuario_id = $guiaMensajeriaTO->getUsuarioId();
                $guiaMensajeria->contenido = $guiaMensajeriaTO->getContenido();
                $guiaMensajeria->comercio_id = $guiaMensajeriaTO->getComercioId();
                $guiaMensajeria->mensajeria_id = $guiaMensajeriaTO->getMensajeriaId();
                $guiaMensajeria->origen = $guiaMensajeriaTO->getOrigen();
                $guiaMensajeria->notificacion = $guiaMensajeriaTO->getNotificacion();
                $guiaMensajeria->numero_externo = $bitacoraCotizacionMensajeriaTO->getNumeroExterno()??'';
                $guiaMensajeria->clave_producto_sat= $guiaMensajeriaTO->getClaveProductoSAT()??'';

                $guiaMensajeria->save();
                $guiaMensajeriaTO->setId($guiaMensajeria->id);

                if($guiaMensajeriaTO->getGuiaMensajeriaDocumentos()){
                    $this->guardarGuiaMensajeriaDocumentos($guiaMensajeriaTO->getGuiaMensajeriaDocumentos(),$guiaMensajeria->id);
                }else{
                    $guiaMensajeriaDocumentoTO->setGuiaMensajeriaId($guiaMensajeria->id);
                    $this->guardarGuiaMensajeriaDocumento($guiaMensajeriaDocumentoTO);
                }
                if ($guiaMensajeriaTO->getGuiaMensajeriaRecoleccionTO()) {
                    $guiaMensajeriaRecoleccionTO = $guiaMensajeriaTO->getGuiaMensajeriaRecoleccionTO();
                    $guiaMensajeriaRecoleccionTO->setGuiaMensajeriaId($guiaMensajeria->id);
                    $this->guardarGuiaMensajeriaRecoleccion($guiaMensajeriaRecoleccionTO);
                }
                if ($guiaMensajeriaTO->getGuiaMensajeriaResponseTO()) {
                    $guiaMensajeriaResponseTO = $guiaMensajeriaTO->getGuiaMensajeriaResponseTO();
                    $this->guardarGuiaMensajeriaResponse($guiaMensajeriaResponseTO);
                }

                if ($guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO()->getEnvioInternacional()) {
                    $guiaInternacionalTO = $guiaMensajeriaTO->getGuiaInternacionalTO();
                    $guiaInternacionalTO->setIdGuiaMensajeria($guiaMensajeria->id);
                    $guiaInternacionalTO->setIdBitacoraCotizacion($guiaMensajeria->bitacora_cotizacion_mensajeria_id);
//                    die(print_r($guiaInternacionalTO));
                    $this->guardarGuiaInternacional($guiaInternacionalTO);
                }

//                die(var_dump($pgs));
//                if($pgs)
//                    $this->descuentaSaldo($guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO());

                # Quitar Codigo comentado para enviar mail
//                $guiaMensajeria->envioMail($guiaMensajeriaTO->getRutaArchivo());
//                unlink($guiaMensajeriaTO->getRutaArchivo());
            }
        );
    }

    public function guardarGuiMensajeriaSSO(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null)
    {

//        die(print_r($guiaMensajeriaTO->getGuiaInternacionalTO()));
        $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $bitacoraMensajeriaDestinoTO = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $guiaMensajeriaDocumentoTO = $guiaMensajeriaTO->getGuiaMensajeriaDocumentoTO();
        $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $this->guardarBitacoraMensajeriaOrigen($bitacoraMensajeriaOrigenTO);
        $this->guardarBitacoraMensajeriaDestino($bitacoraMensajeriaDestinoTO);
        $guiaMensajeria = new GuiaMensajeria();
        $guiaMensajeria->guia = $guiaMensajeriaTO->getGuia();
        $guiaMensajeria->bitacora_cotizacion_mensajeria_id = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId();
        $guiaMensajeria->bitacora_mensajeria_origen_id = $bitacoraMensajeriaOrigenTO->getId();
        $guiaMensajeria->bitacora_mensajeria_destino_id = $bitacoraMensajeriaDestinoTO->getId();
        $guiaMensajeria->usuario_id = $guiaMensajeriaTO->getUsuarioId();
        $guiaMensajeria->contenido = $guiaMensajeriaTO->getContenido();
        $guiaMensajeria->comercio_id = $guiaMensajeriaTO->getComercioId();
        $guiaMensajeria->mensajeria_id = $guiaMensajeriaTO->getMensajeriaId();
        $guiaMensajeria->origen = $guiaMensajeriaTO->getOrigen();
        $guiaMensajeria->notificacion = $guiaMensajeriaTO->getNotificacion();
        $guiaMensajeria->numero_externo = $bitacoraCotizacionMensajeriaTO->getNumeroExterno()??'';
        $guiaMensajeria->clave_producto_sat= $guiaMensajeriaTO->getClaveProductoSAT()??'';

        $guiaMensajeria->save();
        $guiaMensajeriaTO->setId($guiaMensajeria->id);

        if($guiaMensajeriaTO->getGuiaMensajeriaDocumentos()){
            $this->guardarGuiaMensajeriaDocumentos($guiaMensajeriaTO->getGuiaMensajeriaDocumentos(),$guiaMensajeria->id);
        }else{
            $guiaMensajeriaDocumentoTO->setGuiaMensajeriaId($guiaMensajeria->id);
            $this->guardarGuiaMensajeriaDocumento($guiaMensajeriaDocumentoTO);
        }
        if ($guiaMensajeriaTO->getGuiaMensajeriaRecoleccionTO()) {
            $guiaMensajeriaRecoleccionTO = $guiaMensajeriaTO->getGuiaMensajeriaRecoleccionTO();
            $guiaMensajeriaRecoleccionTO->setGuiaMensajeriaId($guiaMensajeria->id);
            $this->guardarGuiaMensajeriaRecoleccion($guiaMensajeriaRecoleccionTO);
        }
        if ($guiaMensajeriaTO->getGuiaMensajeriaResponseTO()) {
            $guiaMensajeriaResponseTO = $guiaMensajeriaTO->getGuiaMensajeriaResponseTO();
            $this->guardarGuiaMensajeriaResponse($guiaMensajeriaResponseTO);
        }

        if ($guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO()->getEnvioInternacional()) {
            $guiaInternacionalTO = $guiaMensajeriaTO->getGuiaInternacionalTO();
            $guiaInternacionalTO->setIdGuiaMensajeria($guiaMensajeria->id);
            $guiaInternacionalTO->setIdBitacoraCotizacion($guiaMensajeria->bitacora_cotizacion_mensajeria_id);
//                    die(print_r($guiaInternacionalTO));
            $this->guardarGuiaInternacional($guiaInternacionalTO);
        }
    }

    /**
     * Metodo que guarda los datos de origen en la mensajeria
     * @param BitacoraMensajeriaOrigenTO $bitacoraMensajeriaOrigenTO
     */
    private function guardarBitacoraMensajeriaOrigen(BitacoraMensajeriaOrigenTO $bitacoraMensajeriaOrigenTO)
    {
        $bitacoraMensajeriaOrigen = new BitacoraMensajeriaOrigen();
        $bitacoraMensajeriaOrigen->nombre = $bitacoraMensajeriaOrigenTO->getNombre();
        $bitacoraMensajeriaOrigen->apellidos = $bitacoraMensajeriaOrigenTO->getApellidos();
        $bitacoraMensajeriaOrigen->email = $bitacoraMensajeriaOrigenTO->getEmail();
        $bitacoraMensajeriaOrigen->calle = $bitacoraMensajeriaOrigenTO->getCalle();
        $bitacoraMensajeriaOrigen->numero = $bitacoraMensajeriaOrigenTO->getNumero();
        $bitacoraMensajeriaOrigen->colonia = $bitacoraMensajeriaOrigenTO->getColonia();
        $bitacoraMensajeriaOrigen->municipio = $bitacoraMensajeriaOrigenTO->getMunicipio();
        $bitacoraMensajeriaOrigen->telefono = $bitacoraMensajeriaOrigenTO->getTelefono();
        $bitacoraMensajeriaOrigen->estado = $bitacoraMensajeriaOrigenTO->getEstado();
        $bitacoraMensajeriaOrigen->referencias = $bitacoraMensajeriaOrigenTO->getReferencias();
        $bitacoraMensajeriaOrigen->usuario_id = $bitacoraMensajeriaOrigenTO->getUsuarioId();
        $bitacoraMensajeriaOrigen->save();
        $bitacoraMensajeriaOrigenTO->setId($bitacoraMensajeriaOrigen->id);
    }

    /**
     * Metodo que guarda los datos de destino en la mensajeria
     * @param BitacoraMensajeriaDestinoTO $bitacoraMensajeriaDestinoTO
     */
    private function guardarBitacoraMensajeriaDestino(BitacoraMensajeriaDestinoTO $bitacoraMensajeriaDestinoTO)
    {
        $bitacoraMensajeriaDestino = new BitacoraMensajeriaDestino();
        $bitacoraMensajeriaDestino->nombre = $bitacoraMensajeriaDestinoTO->getNombre();
        $bitacoraMensajeriaDestino->apellidos = $bitacoraMensajeriaDestinoTO->getApellidos();
        $bitacoraMensajeriaDestino->email = $bitacoraMensajeriaDestinoTO->getEmail();
        $bitacoraMensajeriaDestino->calle = $bitacoraMensajeriaDestinoTO->getCalle();
        $bitacoraMensajeriaDestino->numero = $bitacoraMensajeriaDestinoTO->getNumero();
        $bitacoraMensajeriaDestino->colonia = $bitacoraMensajeriaDestinoTO->getColonia();
        $bitacoraMensajeriaDestino->municipio = $bitacoraMensajeriaDestinoTO->getMunicipio();
        $bitacoraMensajeriaDestino->telefono = $bitacoraMensajeriaDestinoTO->getTelefono();
        $bitacoraMensajeriaDestino->estado = $bitacoraMensajeriaDestinoTO->getEstado();
        $bitacoraMensajeriaDestino->referencias = $bitacoraMensajeriaDestinoTO->getReferencias();
        $bitacoraMensajeriaDestino->usuario_id = $bitacoraMensajeriaDestinoTO->getUsuarioId();
        $bitacoraMensajeriaDestino->save();
        $bitacoraMensajeriaDestinoTO->setId($bitacoraMensajeriaDestino->id);
    }

    /**
     * 
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection|mixed
     */
    public function buscarGuiaMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = GuiaMensajeria::query();
        $guiasMensajerias->when(
            $guiaMensajeriaTO->getGuia(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->where('guia', '=', $guiaMensajeriaTO->getGuia());
            }
        );
        $guiasMensajerias->when(
            $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->where(
                    'bitacora_cotizacion_mensajeria_id',
                    '=',
                    $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId()
                );
            }
        );
        $guiasMensajerias->when(
            $guiaMensajeriaTO->getStatusEntrega(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->where('status_entrega', '=', $guiaMensajeriaTO->getStatusEntrega());
            }
        );
        return $guiasMensajerias->get();
    }

    /**
     * Metodo que busca la cotizacion por el id
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed
     */
    public function findBitacoraCotizacionMensajeria(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
        return BitacoraCotizacionMensajeria::find($bitacoraCotizacionMensajeriaTO->getId());
    }

    /**
     * Metodo que busca la cotizacion por el token
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed
     */
    public function findBitacoraCotizacionMensajeriaByToken(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
        return BitacoraCotizacionMensajeria::Where('token', $bitacoraCotizacionMensajeriaTO->getToken())->first();
    }

    /**
     * Metodo que busca la mensajeria por el id
     * @param MensajeriaTO $mensajeriaTO
     * @return mixed
     */
    public function findMensajeria(MensajeriaTO $mensajeriaTO)
    {
        return Mensajeria::find($mensajeriaTO->getId());
    }

    /**
     * Metodo que guarda los documentos extraidos de el webService de la mensajeria
     * @param GuiaMensajeriaDocumentoTO $guiaMensajeriaDocumentoTO
     */
    private function guardarGuiaMensajeriaDocumento(GuiaMensajeriaDocumentoTO $guiaMensajeriaDocumentoTO)
    {
        $guiaMensajeriaDocumento = new GuiaMensajeriaDocumento();
        $guiaMensajeriaDocumento->guia_mensajeria_id = $guiaMensajeriaDocumentoTO->getGuiaMensajeriaId();
        $guiaMensajeriaDocumento->documento = "";
        $guiaMensajeriaDocumento->extension = $guiaMensajeriaDocumentoTO->getExtension();
        $guiaMensajeriaDocumento->usuario_id = $guiaMensajeriaDocumentoTO->getUsuarioId();
        $guiaMensajeriaDocumento->ruta = $guiaMensajeriaDocumentoTO->getRuta();
        $guiaMensajeriaDocumento->save();
    }

    private function guardarGuiaMensajeriaDocumentos(array $guiaMensajeriaDocumentos,$idGuiaMensajeria)
    {
        foreach ($guiaMensajeriaDocumentos as $documento){
            $guiaMensajeriaDocumento = new GuiaMensajeriaDocumento();
            $guiaMensajeriaDocumento->guia_mensajeria_id = $idGuiaMensajeria;
            $guiaMensajeriaDocumento->documento = "";
            $guiaMensajeriaDocumento->extension = $documento->getExtension();
            $guiaMensajeriaDocumento->usuario_id = $documento->getUsuarioId();
            $guiaMensajeriaDocumento->ruta = $documento->getRuta();
            $guiaMensajeriaDocumento->save();
        }

    }

    /**
     * Guardar la respuesta generada para hacer la peticion y la respuesta
     * @param BitacoraCotizacionMensajeriaResponseTO $bitacoraCotizacionMensajeriaResponseTO
     */
    private function guardarBitacoraCotizacionMensajeriaResponse(
        BitacoraCotizacionMensajeriaResponseTO $bitacoraCotizacionMensajeriaResponseTO
    )
    {
        $bitacoraCotizacionMensajeriaResponse = new BitacoraCotizacionMensajeriaResponse();
        $bitacoraCotizacionMensajeriaResponse->bitacora_cotizacion_mensajeria_id
            = $bitacoraCotizacionMensajeriaResponseTO->getBitacoraCotizacionMensajeriaId();
        $bitacoraCotizacionMensajeriaResponse->request = $bitacoraCotizacionMensajeriaResponseTO->getRequest();
        $bitacoraCotizacionMensajeriaResponse->response = $bitacoraCotizacionMensajeriaResponseTO->getResponse();
        $bitacoraCotizacionMensajeriaResponse->usuario_id = $bitacoraCotizacionMensajeriaResponseTO->getUsuarioId();
        $bitacoraCotizacionMensajeriaResponse->numero_externo = $bitacoraCotizacionMensajeriaResponseTO->getNumeroExterno();
        $bitacoraCotizacionMensajeriaResponse->codigo_respuesta = $bitacoraCotizacionMensajeriaResponseTO->getCodigoRespuesta();
//        die(print_r($bitacoraCotizacionMensajeriaResponse));
        $bitacoraCotizacionMensajeriaResponse->save();
    }

    /**
     * Metodo que busca las Mensajerias de acuerdo a un arreglo de id's
     * @param $arrayMensajeriasId
     * @return mixed
     */
    public function buscarMensajeriasByIds($arrayMensajeriasId)
    {
        return Mensajeria::whereIn('id', $arrayMensajeriasId)->get();
    }

    /**
     * Metodo que modifica el estatus de la guia
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     */
    public function modificarGuiaMensajeriaStatus(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        GuiaMensajeria::where('id', $guiaMensajeriaTO->getId())
            ->update([
                'status_entrega' => $guiaMensajeriaTO->getStatusEntrega(),
                'fecha_status_entrega' => $guiaMensajeriaTO->getFechaStatusEntrega()
            ]);
    }

    /**
     * Metodo que guardar un arreglo de cotizacion realizada en las mensajerias
     * @param array $arrayBitacoraCotizacionMensajeriaTO
     */
    public function guardarArrayBitacoraCotizacionMensajeriaTO(array $arrayBitacoraCotizacionMensajeriaTO)
    {
        
        DB::transaction(
            function () use ($arrayBitacoraCotizacionMensajeriaTO) {
                foreach ($arrayBitacoraCotizacionMensajeriaTO as $bitacoraCotizacionMensajeriaTO) {
                    if ($bitacoraCotizacionMensajeriaTO instanceof BitacoraCotizacionMensajeriaTO) {
                        $this->guardarBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeriaTO);
                    }
                }
            }
        );
    }

    /**
     * Metodo que guarda la recoleccion realizada
     * @param GuiaMensajeriaRecoleccionTO $guiaMensajeriaRecoleccionTO
     */
    public function guardarGuiaMensajeriaRecoleccion(GuiaMensajeriaRecoleccionTO $guiaMensajeriaRecoleccionTO)
    {
        //Log::info('Entra guardarGuiaMensajeriaRecoleccion');
        $guiaMensajeriaRecoleccion = new GuiaMensajeriaRecoleccion();
        $guiaMensajeriaRecoleccion->guia_mensajeria_id = $guiaMensajeriaRecoleccionTO->getGuiaMensajeriaId();
        $guiaMensajeriaRecoleccion->pick_up = $guiaMensajeriaRecoleccionTO->getPickUp();
        $guiaMensajeriaRecoleccion->localizacion = $guiaMensajeriaRecoleccionTO->getLocalizacion();
        $guiaMensajeriaRecoleccion->usuario_id = $guiaMensajeriaRecoleccionTO->getUsuarioId();
        $guiaMensajeriaRecoleccion->fecha_recoleccion = $guiaMensajeriaRecoleccionTO->getFechaRecoleccion();
        $guiaMensajeriaRecoleccion->save();
        if ($guiaMensajeriaRecoleccionTO->getGuiaMensajeriaRecoleccionResponseTO()) {
            $guiaMensajeriaRecoleccionResponseTO = $guiaMensajeriaRecoleccionTO
                ->getGuiaMensajeriaRecoleccionResponseTO();
            $guiaMensajeriaRecoleccionResponseTO
                ->setGuiaMensajeriaRecoleccionId($guiaMensajeriaRecoleccion->id);
            $this->guardarGuiaMensajeriaRecoleccionResponse($guiaMensajeriaRecoleccionResponseTO);
        }
    }

    public function guardarMensajeriaRecoleccion(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        Log::info('Entra guardar recolección');
        $datos = $mensajeriaRecoleccionTO->getDatos();
        $recoleccion = new MensajeriaRecoleccion();
        $recoleccion->numero_pickup = $mensajeriaRecoleccionTO->getPickUp();
        $recoleccion->mensajeria_id = $mensajeriaRecoleccionTO->getmensajeria()->id;
        $recoleccion->nombre_contacto = $datos['nombre_contacto'];
        $recoleccion->apellido_contacto = $datos['apellidos_contacto'];
        $recoleccion->telefono = $datos['telefono'];
        $recoleccion->calle = $datos['calle'];
        $recoleccion->numero = $datos['numero'];
        $recoleccion->colonia = $datos['colonia'];
        $recoleccion->codigo_postal = $datos['codigo_postal'];
        $recoleccion->municipio = $datos['municipio'];
        $recoleccion->estado = $datos['estado'];
        $recoleccion->fecha_pickup = $datos['fecha'];
        $recoleccion->hora_inicio = $datos['hora_inicio'] . ':00';
        $recoleccion->horario_cierre = $datos['horario_cierre'] . ':00';
        $recoleccion->cantidad_piezas = $datos['cantidad_piezas'];
        $recoleccion->peso = $datos['peso'];
        $recoleccion->largo = $datos['largo'];
        $recoleccion->ancho = $datos['ancho'];
        $recoleccion->alto = $datos['alto'];
        $recoleccion->referencias = $datos['referencias'];
        $recoleccion->localizacion = $mensajeriaRecoleccionTO->getLocalizacion();
        $recoleccion->usuario_id = $mensajeriaRecoleccionTO->getUsuarioId();
        $recoleccion->id_comercio= $mensajeriaRecoleccionTO->getComercioId();
        $recoleccion->guias= $mensajeriaRecoleccionTO->getGuias();
//        die(print_r($recoleccion->toArray()));
        $recoleccion->save();
        Log::info('Guarda recoleccion: ' . $recoleccion->numero_pickup);

    }

    public function buscarConfiguracionesMensajeriasUsuariosByIds(
        ConfiguracionMensajeriaUsuarioTO $configuracionMensajeriaUsuarioTO,
        $arrayMensajeriasId
    )
    {
        $configuracionesMensajeriasUsuarios = ConfiguracionMensajeriaUsuario::query();
        $configuracionesMensajeriasUsuarios->whereIn('mensajeria_id', $arrayMensajeriasId);
        $configuracionesMensajeriasUsuarios->where('usuario_id', '=', $configuracionMensajeriaUsuarioTO->getUsuarioId());
        return $configuracionesMensajeriasUsuarios->get();
    }

    public function buscarFormatosImpresionMensajerias(
        FormatoGuiaImpresionMensajeriaTO $formatoGuiaImpresionMensajeriaTO,
        $arrayMensajeriasId
    )
    {
        $formatosGuiasImpresionMensajerias = FormatoGuiaImpresionMensajeria::query();
        $formatosGuiasImpresionMensajerias->whereIn('mensajeria_id', $arrayMensajeriasId);
        $formatosGuiasImpresionMensajerias->when(
            $formatoGuiaImpresionMensajeriaTO->getDefault() !== null,
            function ($query) use ($formatoGuiaImpresionMensajeriaTO) {
                $query->where('default', '=', $formatoGuiaImpresionMensajeriaTO->getDefault());
            }
        );
        return $formatosGuiasImpresionMensajerias->get();
    }


    public function buscarGuiasMensajeriasResumen(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = $this->guiasMensajerias($guiaMensajeriaTO)
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5])
          //  ->where('guias_mensajerias.estatus','!=',2)
            ->select(DB::raw('count(*) as totalGuias'),
            DB::raw('sum(costo_porcentaje) as totalCosto'),
            DB::raw('sum(DATEDIFF(IFNULL(guias_mensajerias.fecha_status_entrega, now()), guias_mensajerias.created_at)) diasTranscurridos'));
        return $guiasMensajerias->first();
    }

    public function buscarGuiasMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        //guias de la grafica de guias por dia y mensajeria
        $guiasMensajerias = $this->guiasMensajerias($guiaMensajeriaTO)
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5])
            ->where('guias_mensajerias.estatus','!=',2)
            ->select(
                'guias_mensajerias.guia', 'mensajerias.clave',
                'bitacoras_cotizaciones_mensajerias.costo',
                'bitacoras_cotizaciones_mensajerias.costo_porcentaje',
                DB::raw('guias_mensajerias.created_at fecha_creacion'),
                'guias_mensajerias.fecha_status_entrega',
                DB::raw('DATEDIFF(IFNULL(guias_mensajerias.fecha_status_entrega, now()), guias_mensajerias.created_at) dias_transcurridos'),
                DB::raw('if(guias_mensajerias.status_entrega = 10, "Entregado", "Generado") status')
            );
        return $guiasMensajerias->get();
    }

    public function buscarGuiasCostos(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = $this->guiasMensajerias($guiaMensajeriaTO)
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5])
            ->select('bitacoras_cotizaciones_mensajerias.mensajeria_id', DB::raw('count(*) as total'),
                'mensajerias.clave',
                DB::raw('ROUND(sum(bitacoras_cotizaciones_mensajerias.costo_porcentaje),2) as total_costo'),
                DB::raw('sum(DATEDIFF(IFNULL(guias_mensajerias.fecha_status_entrega, now()), guias_mensajerias.created_at)) dias_transcurridos'),
                'guias_mensajerias.status_entrega'
            )->groupBy('bitacoras_cotizaciones_mensajerias.mensajeria_id');
        return $guiasMensajerias->get();
    }

    public function buscarGuiasMensajeriasTotales(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = $this->guiasMensajerias($guiaMensajeriaTO)
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5])
            ->select('bitacoras_cotizaciones_mensajerias.mensajeria_id', DB::raw('count(*) as total'))
            ->groupBy('bitacoras_cotizaciones_mensajerias.mensajeria_id');
           // die(print_r($guiasMensajerias->toSql()));
        return $guiasMensajerias->get();
    }

    private function guardarGuiaMensajeriaResponse(GuiaMensajeriaResponseTO $guiaMensajeriaResponseTO)
    {
        //Log::info('Inicia Guardar guia mensajeria response');
        $guiaMensajeriaResponse = new GuiaMensajeriaResponse();
        $guiaMensajeriaResponse->guia_mensajeria_id = $guiaMensajeriaResponseTO->getGuiaMensajeriaId();
        $guiaMensajeriaResponse->request = $guiaMensajeriaResponseTO->getRequest();
        $guiaMensajeriaResponse->response = $guiaMensajeriaResponseTO->getResponse();
        $guiaMensajeriaResponse->usuario_id = $guiaMensajeriaResponseTO->getUsuarioId();
        $guiaMensajeriaResponse->codigo_respuesta = $guiaMensajeriaResponseTO->getCodigoRespuesta();
        $guiaMensajeriaResponse->save();
    }

    private function guardarGuiaMensajeriaRecoleccionResponse(
        GuiaMensajeriaRecoleccionResponseTO $guiaMensajeriaRecoleccionResponseTO
    )
    {
        $guiaMensajeriaRecoleccionResponse = new GuiaMensajeriaRecoleccionResponse();
        $guiaMensajeriaRecoleccionResponse->guia_mensajeria_recoleccion_id
            = $guiaMensajeriaRecoleccionResponseTO->getGuiaMensajeriaRecoleccionId();
        $guiaMensajeriaRecoleccionResponse->request
            = $guiaMensajeriaRecoleccionResponseTO->getRequest();
        $guiaMensajeriaRecoleccionResponse->response
            = $guiaMensajeriaRecoleccionResponseTO->getResponse();
        $guiaMensajeriaRecoleccionResponse->usuario_id
            = $guiaMensajeriaRecoleccionResponseTO->getUsuarioId();
        $guiaMensajeriaRecoleccionResponse->save();
    }

    private function guardarGuiaInternacional(GuiaInternacionalTO $guiaInternacionalTO)
    {
        //Log::info('Inicia Guardar guia Internacional');
        $guiaInternacional = new GuiaInternacional();
        $guiaInternacional->id_guia_mesajeria = $guiaInternacionalTO->getIdGuiaMensajeria();
        $guiaInternacional->pais_destino = $guiaInternacionalTO->getPaisDestino();
        $guiaInternacional->proposito_envio = $guiaInternacionalTO->getPropositoEnvio();
        $guiaInternacional->pais_fabricacion = $guiaInternacionalTO->getPaisFabricacion();
        $guiaInternacional->total_envios = $guiaInternacionalTO->getTotalEnvios();
        $guiaInternacional->moneda = $guiaInternacionalTO->getMoneda();
        $guiaInternacional->id_bitacora_cotizacion = $guiaInternacionalTO->getIdBitacoraCotizacion();
        $guiaInternacional->save();
    }

    public function guardaConfiguracionLlaves(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTo)
    {
        $accesoComercioMensajeria = new AccesoComercioMensajeria();
        $accesoComercioMensajeria->acceso_campo_mensajeria_id = $accesoComercioMensajeriaTo->getAccesoCampoMensajeriaId();
        $accesoComercioMensajeria->mensajeria_id = $accesoComercioMensajeriaTo->getMensajeriaId();
        $accesoComercioMensajeria->comercio_id = $accesoComercioMensajeriaTo->getComercioId();
        $accesoComercioMensajeria->valor = $accesoComercioMensajeriaTo->getValor();
        $accesoComercioMensajeria->save();
    }

    public function buscarCotizacionesResumen($fechaInicio, $fechaFin, $mensajeriaId, $comercioId)
    {
        $bitacoraCotizacionesMensajeria = BitacoraCotizacionMensajeria::select('mensajeria_id', 'mensajerias.clave',
            DB::raw('count(*) as total'))
            ->join('mensajerias', 'mensajerias.id', '=', 'bitacoras_cotizaciones_mensajerias.mensajeria_id');
        $bitacoraCotizacionesMensajeria->when(!is_null($fechaInicio) && !is_null($fechaInicio),
            function ($query) use ($fechaInicio, $fechaFin) {
                $query->where('bitacoras_cotizaciones_mensajerias.created_at', '>=', $fechaInicio . ' 00:00:00')
                    ->where('bitacoras_cotizaciones_mensajerias.created_at', '<=', $fechaFin . ' 23:59:59');
            }
        );
        $bitacoraCotizacionesMensajeria->when($mensajeriaId,
            function ($query) use ($mensajeriaId) {
                $query->where('mensajeria_id', '=', $mensajeriaId);
            }
        );

        $bitacoraCotizacionesMensajeria->when($comercioId,
            function ($query) use ($comercioId) {
                $query->where('comercio_id', '=', $comercioId);
            }
        );
        $bitacoraCotizacionesMensajeria->groupBy('mensajeria_id');
        //die($bitacoraCotizacionesMensajeria->toSql());
        return $bitacoraCotizacionesMensajeria->get();
    }

    private function guiasMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        // die("<pre>".print_r($guiaMensajeriaTO));
        $guiasMensajerias = GuiaMensajeria::query();
        $guiasMensajerias->when(
            !is_null($guiaMensajeriaTO->getFechaInicio())
            && !is_null($guiaMensajeriaTO->getFechaInicio()),
            function ($query) use ($guiaMensajeriaTO) {
                $query->where('guias_mensajerias.created_at', '>=', "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00")
                    ->where('guias_mensajerias.created_at', '<=', "{$guiaMensajeriaTO->getFechaFin()} 23:59:59");
            }
        );
        $guiasMensajerias->when(
            $guiaMensajeriaTO->getMensajeriaId(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->whereHas(
                    'bitacoraCotizacionMensajeria',
                    function ($query) use ($guiaMensajeriaTO) {
                        $query->where('mensajeria_id', '=', $guiaMensajeriaTO->getMensajeriaId());
                    }
                );
            }
        );

        $guiasMensajerias->when(
            $guiaMensajeriaTO->getComercioId(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->whereHas(
                    'bitacoraCotizacionMensajeria',
                    function ($query) use ($guiaMensajeriaTO) {
                        $query->where('comercio_id', '=', $guiaMensajeriaTO->getComercioId());
                    }
                );
            }
        );

        $guiasMensajerias->when($guiaMensajeriaTO->getComercioId(), function ($query) use ($guiaMensajeriaTO) {
            $query->where('guias_mensajerias.comercio_id', '=', $guiaMensajeriaTO->getComercioId());
        });



        $guiasMensajerias->when(
            $guiaMensajeriaTO->getStatusEntrega(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->where('status_entrega', '=', $guiaMensajeriaTO->getStatusEntrega());
            }
        );

        $guiasMensajerias
            ->join(
                'bitacoras_cotizaciones_mensajerias',
                'bitacoras_cotizaciones_mensajerias.id',
                '=',
                'guias_mensajerias.bitacora_cotizacion_mensajeria_id'
            )
            ->join('mensajerias', 'mensajerias.id', '=', 'bitacoras_cotizaciones_mensajerias.mensajeria_id');

        return $guiasMensajerias;
    }

    public function topGuiasDestino(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = $this->topGuias($guiaMensajeriaTO, 'bitacoras_cotizaciones_mensajerias.codigo_postal_destino')
            ->groupBy('codigo_postal_destino')
            ->groupBy('mensajeria_id');
        return $guiasMensajerias->get();
    }

    public function guiasPorEstado(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo)
    {
        $codidosPostales = GuiaMensajeria::select('bitacoras_cotizaciones_mensajerias.'. $tipo)
        ->join('bitacoras_cotizaciones_mensajerias', 'bitacoras_cotizaciones_mensajerias.id', '=', 'guias_mensajerias.bitacora_cotizacion_mensajeria_id')
        ->where('guias_mensajerias.created_at', '>=', "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00")
        ->where('guias_mensajerias.created_at', '<=', "{$guiaMensajeriaTO->getFechaFin()}  23:59:59")
        ->whereNotIn('guias_mensajerias.status_entrega', [3, 5])
        ->where('bitacoras_cotizaciones_mensajerias.envio_internacional', 0)
        ->where('guias_mensajerias.estatus', '!=', 2)
        ->distinct()
        ->get();

        $subquery = GuiaMensajeria::select('sepomex.c_estado', 'bitacoras_cotizaciones_mensajerias.'. $tipo)
        ->join('bitacoras_cotizaciones_mensajerias', 'bitacoras_cotizaciones_mensajerias.id', '=', 'guias_mensajerias.bitacora_cotizacion_mensajeria_id')
        ->join('sepomex', 'sepomex.d_codigo', '=', 'bitacoras_cotizaciones_mensajerias.'. $tipo)
        ->where('guias_mensajerias.created_at', '>=',  "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00")
        ->where('guias_mensajerias.created_at', '<=', "{$guiaMensajeriaTO->getFechaFin()}  23:59:59")
        ->whereNotIn('guias_mensajerias.status_entrega', [3, 5])
            ->where('bitacoras_cotizaciones_mensajerias.envio_internacional', 0)
            ->where('guias_mensajerias.estatus', '!=', 2)
            ->whereIn('sepomex.d_codigo', $codidosPostales)
            ->groupBy('guias_mensajerias.guia');

        $guiasMensajerias = DB::table(DB::raw("({$subquery->toSql()}) as XEstado"))
        ->mergeBindings($subquery->getQuery())
            ->select('c_estado.descripcion as estado', 'c_estado.abrev', DB::raw('COUNT(XEstado.'. $tipo.') as total'))
            ->join('c_estado', 'c_estado.clave', '=', 'XEstado.c_estado')
            ->groupBy('c_estado.clave');

        //->toSql();
         // die(print_r($guiasMensajerias->get()));
       
        $guiasMensajerias->when(
            $guiaMensajeriaTO->getComercioId(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->where('guias_mensajerias.comercio_id', '=', $guiaMensajeriaTO->getComercioId());
            }
        );

        return $guiasMensajerias->get();
    }

    public function guiasPorEstadoMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo, $codigoEstado)
    {
        $subquery = GuiaMensajeria::select('c_estado.descripcion as estado', 'c_estado.abrev', 'bitacoras_cotizaciones_mensajerias.'.$tipo,
         'guias_mensajerias.mensajeria_id', 'guias_mensajerias.guia', DB::raw('mensajerias.clave as mensajeria'))
        ->join('bitacoras_cotizaciones_mensajerias', 'bitacoras_cotizaciones_mensajerias.id', '=', 'guias_mensajerias.bitacora_cotizacion_mensajeria_id')
        ->join('sepomex', 'sepomex.d_codigo', '=', 'bitacoras_cotizaciones_mensajerias.'.$tipo)
        ->join('c_estado', 'c_estado.clave', '=', 'sepomex.c_estado')
        ->join('mensajerias', 'mensajerias.id', '=', 'guias_mensajerias.mensajeria_id')
        ->where('guias_mensajerias.created_at', '>=',  "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00")
        ->where('guias_mensajerias.created_at', '<=', "{$guiaMensajeriaTO->getFechaFin()}  23:59:59")
        ->whereNotIn('guias_mensajerias.status_entrega', [3, 5])
        ->where('guias_mensajerias.estatus', '!=', 2)
        ->where('c_estado.abrev', $codigoEstado)
        ->groupBy('guias_mensajerias.guia');
        //die(print_r($subquery->get()->toArray()));
        
        $guiasMensajerias = DB::table(DB::raw("({$subquery->toSql()}) as XMensajeria"))
        ->mergeBindings($subquery->getQuery())
        ->select($tipo, 'mensajeria_id', 'mensajeria',DB::raw('COUNT(mensajeria_id) as total'))
        ->groupBy($tipo, 'mensajeria_id');
        //->toSql();
        //die(print_r($guiasMensajerias->get()));

        $guiasMensajerias->when(
            $guiaMensajeriaTO->getComercioId(),
            function ($query) use ($guiaMensajeriaTO) {
                $query->where('guias_mensajerias.comercio_id', '=', $guiaMensajeriaTO->getComercioId());
            }
        );

        return $guiasMensajerias->get();
    }

    public function topGuiasOrigen(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = $this->topGuias($guiaMensajeriaTO, 'bitacoras_cotizaciones_mensajerias.codigo_postal_origen')
            ->groupBy('codigo_postal_origen')
            ->groupBy('mensajeria_id');
           // die(print_r($guiasMensajerias->get()->toArray()));
        return $guiasMensajerias->get();
    }

    private function topGuias(GuiaMensajeriaTO $guiaMensajeriaTO, $type)
    {
        $guiasMensajerias = GuiaMensajeria::select(DB::raw("$type as codigo_postal"), 'bitacoras_cotizaciones_mensajerias.mensajeria_id',
            'mensajerias.clave', DB::raw('count(*) as total'))
            ->join('bitacoras_cotizaciones_mensajerias', 'bitacoras_cotizaciones_mensajerias.id', '=', 'guias_mensajerias.bitacora_cotizacion_mensajeria_id')
            ->join('mensajerias', 'mensajerias.id', '=', 'bitacoras_cotizaciones_mensajerias.mensajeria_id')
            ->where('guias_mensajerias.created_at', '>=', "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00")
            ->where('guias_mensajerias.created_at', '<=', "{$guiaMensajeriaTO->getFechaFin()} 23:59:59")
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5])
            ->where('bitacoras_cotizaciones_mensajerias.envio_internacional',0);

        $guiasMensajerias->when($guiaMensajeriaTO->getComercioId(), function ($query) use ($guiaMensajeriaTO) {
            $query->where('guias_mensajerias.comercio_id', '=', $guiaMensajeriaTO->getComercioId());
        }
        );

       
        return $guiasMensajerias;
    }

    public function topCodigosPostalesDestino(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = $this->topGuias($guiaMensajeriaTO, 'bitacoras_cotizaciones_mensajerias.codigo_postal_destino')
            ->groupBy('codigo_postal_destino')
            ->groupBy('mensajeria_id')
            ->limit(20);
        return $guiasMensajerias->get();
    }

    public function topCodigosPostalesOrigen(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiasMensajerias = $this->topGuias($guiaMensajeriaTO, 'bitacoras_cotizaciones_mensajerias.codigo_postal_origen')
            ->groupBy('codigo_postal_origen')
            ->groupBy('mensajeria_id')
            ->limit(20);
            //die(print_r($guiasMensajerias->toSql()));
        return $guiasMensajerias->get();
    }

    public function topComercios(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $topComercio = BitacoraCotizacionMensajeria::select('comercios.id', 'comercios.clave', 'comercios.descripcion',
            'bitacoras_cotizaciones_mensajerias.costo_porcentaje',
            'bitacoras_cotizaciones_mensajerias.costo')
            ->join('guias_mensajerias', 'bitacora_cotizacion_mensajeria_id', '=', 'bitacoras_cotizaciones_mensajerias.id')
            ->join('comercios', 'comercios.id', '=', 'bitacoras_cotizaciones_mensajerias.comercio_id')
            ->where('guias_mensajerias.created_at', '>=', "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00")
            ->where('guias_mensajerias.created_at', '<=', "{$guiaMensajeriaTO->getFechaFin()} 23:59:59")
            ->get();

        return $topComercio;
    }

    public function guardarTrackMensajeriaResponse(TrackMensajeriaResponseTO $trackMensajeriaResponseTO)
    {
        $trackMensajeriaResponse = new TrackMensajeriaResponse();
        $trackMensajeriaResponse->guia_mensajeria_id = $trackMensajeriaResponseTO->getGuiaMensajeriaId();
        $trackMensajeriaResponse->request = $trackMensajeriaResponseTO->getRequest();
        $trackMensajeriaResponse->response = $trackMensajeriaResponseTO->getResponse();
        $trackMensajeriaResponse->usuario_id = $trackMensajeriaResponseTO->getUsuarioId();
        $trackMensajeriaResponse->save();
    }

    public function detalleFacturacion(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = [])
    {
        $detalle = $this->guiasMensajerias($guiaMensajeriaTO)
            ->select('guias_mensajerias.guia', 'bitacoras_cotizaciones_mensajerias.comercio_id', 'bitacoras_cotizaciones_mensajerias.mensajeria_id',
                'bitacoras_cotizaciones_mensajerias.id', 'bitacoras_cotizaciones_mensajerias.seguro', 'bitacoras_cotizaciones_mensajerias.negociacion_id',
                DB::raw('comercios.descripcion as comercio'),
                DB::raw('bitacoras_cotizaciones_mensajerias.costo as costoMensajeriaCotizacion'),
                DB::raw('bitacoras_cotizaciones_mensajerias.costo_cliente as costoCliente'),
                DB::raw('bitacoras_cotizaciones_mensajerias.costo_porcentaje as costoComercioCotizacion'),
                DB::raw('bitacoras_cotizaciones_mensajerias.costo_convenio as costoNegociacion'),
                DB::raw('bitacoras_cotizaciones_mensajerias.porcentaje as porcentajeNegociacion'),
                DB::raw('bitacoras_cotizaciones_mensajerias.porcentaje_seguro'),
                DB::raw('bitacoras_cotizaciones_mensajerias.valor_paquete'),
                DB::raw('bitacoras_cotizaciones_mensajerias.peso as pesoCotizacion'),
                DB::raw('bitacoras_cotizaciones_mensajerias.costo_adicional as costoAdicionalNegociacion'),
                'guias_mensajerias.created_at', 'guias_facturas_mensajerias.numero_factura',
                DB::raw('guias_facturas_mensajerias.costo as costoFactura'),
                DB::raw('guias_facturas_mensajerias.peso as pesoFactura'),
                'guias_excedentes.excedente_peso', 'guias_excedentes.excedente_costo', 'mensajerias.descripcion','guias_mensajerias_documentos.ruta')
            ->leftJoin('guias_facturas_mensajerias', 'guias_facturas_mensajerias.guia', '=', 'guias_mensajerias.guia')
            ->leftJoin('guias_excedentes', 'guias_excedentes.guia', '=', 'guias_mensajerias.guia')
            ->join('comercios', 'comercios.id', '=', 'bitacoras_cotizaciones_mensajerias.comercio_id')
            ->join('guias_mensajerias_documentos', 'guias_mensajerias_documentos.guia_mensajeria_id', '=', 'guias_mensajerias.id')
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5]);

//        die(print_r($params->get('length')));
        if (isset($params['search'])) {
            $detalle->where('guias_mensajerias.guia', $params['search']);
        }

        $result['recordsTotal'] = $detalle->count();

        if (isset($params['length'])) {
            $detalle->take($params['length']);
        }

        if (isset($params['start'])) {
            $detalle->skip($params['start']);
        }

        $result['data'] = $detalle->get();

//        $params = [
//            'draw' => $request->draw,
//            'skip' => $request->start,
//            'take' => $request->length, // Rows display per page
//            'columnIndex' => $request->order, // Column index
//            'columnName' => $request->column, // Column name
//            'columnSortOrder' => $request->order, // asc or desc
//            'searchValue' => $request->search, // Search value
//        ];
        return $result;
    }

    /**
     * Lista de comercios del dashboard
     * @param  GuiaMensajeriaTO  $guiaMensajeriaTO
     * @param  array  $params
     * @return \Illuminate\Database\Eloquent\Builder[]|\Illuminate\Database\Eloquent\Collection
     */
    public function getTotalesGuias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = [])
    {
        $bitacoraCotizacionMensajeria = BitacoraCotizacionMensajeria::query();

        $bitacoraCotizacionMensajeria->select('bitacoras_cotizaciones_mensajerias.comercio_id',
            DB::raw('count(guias_mensajerias.id) as totalGuias'),
            DB::raw('sum(costo) AS costoMensajeria'),
            DB::raw('sum(costo_cliente) as costoCliente'),
            DB::raw('sum(costo_porcentaje) as costoTotal'),
            DB::raw('sum(excedente_peso) as excedentePesoTotal'),
            DB::raw('sum(excedente_costo) as excedenteCostoTotal')
        )
            ->join('guias_mensajerias', 'bitacoras_cotizaciones_mensajerias.id', '=', 'guias_mensajerias.bitacora_cotizacion_mensajeria_id')
            ->leftJoin('guias_excedentes', 'guias_excedentes.guia_mensajeria_id', '=', 'guias_mensajerias.id')
            ->whereBetween('guias_mensajerias.created_at', [
                "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00", "{$guiaMensajeriaTO->getFechaFin()} 23:59:59"])
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5])
            ->groupBy('bitacoras_cotizaciones_mensajerias.comercio_id');

        $bitacoraCotizacionMensajeria->when($guiaMensajeriaTO->getComercioId(), function ($query) use ($guiaMensajeriaTO) {
            $query->where('bitacoras_cotizaciones_mensajerias.comercio_id', '=', $guiaMensajeriaTO->getComercioId());
        });
        //die(print_r($bitacoraCotizacionMensajeria->toSql()));
        $result = $bitacoraCotizacionMensajeria->get();
//        die(print_r($result));
        return $result;
    }

    public function getTotalesMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = [])
    {
        $bitacoraCotizacionMensajeria = BitacoraCotizacionMensajeria::query();

        $bitacoraCotizacionMensajeria->select('bitacoras_cotizaciones_mensajerias.comercio_id',
            'bitacoras_cotizaciones_mensajerias.mensajeria_id',
            DB::raw('count(guias_mensajerias.id) as totalGuias'),
            DB::raw('sum(bitacoras_cotizaciones_mensajerias.costo) AS costoMensajeria'),
            DB::raw('sum(costo_cliente) as costoCliente'),
            DB::raw('sum(costo_porcentaje) as costoTotal'),
            DB::raw('sum(excedente_peso) as excedentePesoTotal'),
            DB::raw('sum(excedente_costo) as excedenteCostoTotal'))
            ->join('guias_mensajerias', 'bitacoras_cotizaciones_mensajerias.id', '=', 'guias_mensajerias.bitacora_cotizacion_mensajeria_id')
            ->leftJoin('guias_excedentes', 'guias_excedentes.guia', '=', 'guias_mensajerias.guia')
            ->whereBetween('guias_mensajerias.created_at', ["{$guiaMensajeriaTO->getFechaInicio()} 00:00:00", "{$guiaMensajeriaTO->getFechaFin()} 23:59:59"])
            ->whereNotIn('guias_mensajerias.status_entrega',[3,5])
            ->groupBy('bitacoras_cotizaciones_mensajerias.mensajeria_id');

        $bitacoraCotizacionMensajeria->when($guiaMensajeriaTO->getComercioId(), function ($query) use ($guiaMensajeriaTO) {
            $query->where('bitacoras_cotizaciones_mensajerias.comercio_id', '=', $guiaMensajeriaTO->getComercioId());
        });

        $bitacoraCotizacionMensajeria->when($guiaMensajeriaTO->getMensajeriaId(), function ($query) use ($guiaMensajeriaTO) {
            $query->where('bitacoras_cotizaciones_mensajerias.mensajeria_id', '=', $guiaMensajeriaTO->getMensajeriaId());
        });

        $result = $bitacoraCotizacionMensajeria->get();
//        die(print_r($result->toArray()));
        return $result;
    }


    public function buscarRecoleccionExistente(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
       // die(print_r($mensajeriaRecoleccionTO->getmensajeria()));
        $datos = $mensajeriaRecoleccionTO->getDatos();
        $recoleccion = MensajeriaRecoleccion::query();
      
        $recoleccion->where('mensajeria_id', $mensajeriaRecoleccionTO->getmensajeria()->id)
            ->where('id_comercio', $mensajeriaRecoleccionTO->getComercioId())
            ->where('calle', $datos['calle'])
            ->where('numero', $datos['numero'])
            ->where('colonia', $datos['colonia'])
            ->where('codigo_postal', $datos['codigo_postal'])
            ->where('municipio', $datos['municipio'])
            ->where('estado', $datos['estado'])
            ->where('fecha_pickup', $datos['fecha'])
            ->get();
//        die(print_r($recoleccion->get()));

        return $recoleccion->first();
    }

    public function buscarRecoleccionGuia($guia)
    {
        $recoleccion = MensajeriaRecoleccion::where('guias', $guia)->first();
//        die(print_r($recoleccion->get()));

        return $recoleccion;
    }

    public function buscarCostoNegociacion(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        $negociacion = CostoMensajeria::query();
        $negociacion->where('comercio_id', $mensajeriaRecoleccionTO->getComercioId())
            ->where('mensajeria_id', $mensajeriaRecoleccionTO->getmensajeria()->id)
            ->get();

        return $negociacion->first();
    }

    public function buscarLimitePrecio(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
//        die(print_r($bitacoraCotizacionMensajeriaTO->getComercioId()));
        $comercioId =  $bitacoraCotizacionMensajeriaTO->getComercioId();
        if($bitacoraCotizacionMensajeriaTO->getNegociacionId() == 1){
            $comercioId = 1;
        }

        $limitePrecio = CampoLimiteMensajeria::leftjoin('configuracion_limites_mensajerias', 'campos_limites_mensajerias.id_limite_mensajeria', '=', 'configuracion_limites_mensajerias.id')
            ->where('configuracion_limites_mensajerias.id', 4)
            ->where('campos_limites_mensajerias.id_comercio', $comercioId)
            ->where('campos_limites_mensajerias.id_mensajeria', $bitacoraCotizacionMensajeriaTO->getMensajeriaId())
            ->first();

        return $limitePrecio;
    }

    public function descuentaSaldo(BitacoraCotizacionMensajeriaTO $cotizacion,$session)
    {
        $fecha = Carbon::now();
        $time = strtotime($fecha->format('Y-m-d H:i:s'));
        $today = new \mongodb\BSON\UTCDateTime($time * 1000);

        $precioGuia = floatval($cotizacion->getCostoTotal());
        $logMessage = ' comercio:'.$cotizacion->getComercioId() .PHP_EOL;
        $saldoActual = SGeneral::where('comercio_id',$cotizacion->getComercioId())->first();

        $logMessage .= ' precio guia:'.$precioGuia .PHP_EOL;
        if($saldoActual){
            $logMessage .= ' saldo actual:'.$saldoActual->saldo_actual .PHP_EOL;

            if($saldoActual->saldo_actual >= $precioGuia){
                $update = DB::connection('mongodb')->collection('s_general')->where('comercio_id',$cotizacion->getComercioId())
                    ->update([
                        '$inc' => array('saldo_actual'=>(-$precioGuia)),
                        '$set'=>['fecha_modificacion'=>$today],

                    ], array('multiple'=>true,'session' => $session));

                $response = ($update == 1)? true : false;
                if($response == false){
                    $session->endSession();
                    throw new \Exception("Error al descontar saldo, comercio: ".$cotizacion->getComercioId());
                }
//                die(var_dump($response));

                return $response ;
            }else{
                $mensaje ="No cuenta con saldo suficiente para este requerimiento 2";
                $session->endSession();
                throw new \Exception($mensaje);
            }
        }else{
            $mensaje ="No cuenta con saldo para este requerimiento";
            $logMessage .= $mensaje .PHP_EOL;
            $session->endSession();
            throw new \Exception($mensaje);
        }
        $logMessage .= 'Termina proceso descuentaSaldo' .PHP_EOL;
        Log::info($logMessage);

        return false;

    }

    public function regresaSaldo(BitacoraCotizacionMensajeriaTO $cotizacion)
    {
        $fecha = Carbon::now();
        $time = strtotime($fecha->format('Y-m-d H:i:s'));
        $today = new \mongodb\BSON\UTCDateTime($time * 1000);

        $precioGuia = floatval($cotizacion->getCostoTotal());
        Log::info(" regresa saldo en caso de haber descontado saldo: comercio-".$cotizacion->getComercioId().', precio-'.$precioGuia);

        DB::connection('mongodb')->collection('s_general')->where('comercio_id',$cotizacion->getComercioId())
            ->update(['$inc' => array('saldo_actual'=>($precioGuia))],['fecha_modificacion'=>$today],[array('multiple'=>true)]);

        $saldo = DB::connection('mongodb')->collection('s_general')->where('comercio_id',$cotizacion->getComercioId())->first();
        Log::info(' saldo retornado actualizado:'.$saldo['saldo_actual']);

    }


    public function desactivaCotizacionesComercio(Array $bicatorasComerciosIds)
    {
        CotizacionPaquete::whereIn('id_cotizaciones_paquetes', $bicatorasComerciosIds)
            ->update([
                'estatus' => false,
            ]);
    }

    public function buscaLista(TrackingMensajeriaTO $trackingMensajeriaTO)
    {
        $logMessage = 'Lista guias comercio: '.Auth::user()->comercio_id.' entra en buscaLista' .PHP_EOL;
        $guiasMensajerias = GuiaMensajeria::select('id','guia','created_at','status_entrega','numero_externo','mensajeria_id')->orderBy('created_at','desc')
        ->where('estatus',1)
        ->whereIn('status_entrega',[1,4,10]);
        $guiasCount = DB::table('guias_mensajerias')->select(DB::raw('COUNT(id) as total'));

        $guiasMensajerias->when($trackingMensajeriaTO->getIdSeller(), function ($query) use ($trackingMensajeriaTO,$guiasCount) {
            $query->where('comercio_id', $trackingMensajeriaTO->getIdSeller());
            $guiasCount->where('comercio_id', $trackingMensajeriaTO->getIdSeller());
        });

        $guiasMensajerias->when($trackingMensajeriaTO->getIdsSellers(), function ($query) use ($trackingMensajeriaTO,$guiasCount) {
            $logMessage = 'Busca guia comercios: ' .PHP_EOL;
            $logMessage .=  $trackingMensajeriaTO->getIdsSellers() .PHP_EOL;
            $query->whereIn('comercio_id', $trackingMensajeriaTO->getIdsSellers());
            $guiasCount->whereIn('comercio_id', $trackingMensajeriaTO->getIdsSellers());
        });

        $guiasMensajerias->when($trackingMensajeriaTO->getGuia(), function ($query) use ($trackingMensajeriaTO,$guiasCount) {
            $logMessage = 'Busca guia: '.$trackingMensajeriaTO->getGuia(). ', Comercio : '.Auth::user()->comercio_id .PHP_EOL;
            $query->where('guia', $trackingMensajeriaTO->getGuia());
            $guiasCount->where('guia', $trackingMensajeriaTO->getGuia());
        });
        
        $guiasMensajerias->when($trackingMensajeriaTO->getFechaInicio(), function ($query) use ($trackingMensajeriaTO,$guiasCount) {
            $logMessage = 'Busca fecha inicio: '.$trackingMensajeriaTO->getFechaInicio(). ', Comercio : '.Auth::user()->comercio_id .PHP_EOL;
            $query->where('created_at', '>=',$trackingMensajeriaTO->getFechaInicio()->startOfDay());
            $guiasCount->where('created_at', '>=',$trackingMensajeriaTO->getFechaInicio()->startOfDay());
        });

        $guiasMensajerias->when($trackingMensajeriaTO->getFechaFin(), function ($query) use ($trackingMensajeriaTO,$guiasCount) {
            $logMessage = 'Busca fecha fin: '.$trackingMensajeriaTO->getFechaFin(). ', Comercio : '.Auth::user()->comercio_id .PHP_EOL;
            $query->where('created_at','<=', $trackingMensajeriaTO->getFechaFin()->endOfDay());
            $guiasCount->where('created_at','<=', $trackingMensajeriaTO->getFechaFin()->endOfDay());
        });

        $guiasMensajerias->when($trackingMensajeriaTO->getIdMensajeria(), function ($query) use ($trackingMensajeriaTO,$guiasCount) {
            $logMessage = 'Busca mensajeria: '.$trackingMensajeriaTO->getIdMensajeria(). ', Comercio : '.Auth::user()->comercio_id .PHP_EOL;
            $query->where('mensajeria_id', $trackingMensajeriaTO->getIdMensajeria());
            $guiasCount->where('mensajeria_id', $trackingMensajeriaTO->getIdMensajeria());
        });

        $guiasMensajerias->when($trackingMensajeriaTO->getNumOrden(), function ($query) use ($trackingMensajeriaTO,$guiasCount) {
            $logMessage = 'Busca numero_externo: '.$trackingMensajeriaTO->getNumOrden(). ', Comercio : '.Auth::user()->comercio_id .PHP_EOL;
            $query->where('numero_externo', $trackingMensajeriaTO->getNumOrden());
            $guiasCount->where('numero_externo', $trackingMensajeriaTO->getNumOrden());
        });
        
        $logMessage .= 'Termina filtros guias comercio: '.Auth::user()->comercio_id.' busca '.$trackingMensajeriaTO->getLimit().' guias' .PHP_EOL;
        
        //$guiasFiltardas = clone $guiasMensajerias;
        $total = $guiasCount->get()[0]->total;
        $logMessage .= 'termina total ' . $total . ' registros guias, comercio:' . Auth::user()->comercio_id .PHP_EOL;
      // die(print_r($total));
        $guias = $guiasMensajerias
            ->skip($trackingMensajeriaTO->getLimit()*$trackingMensajeriaTO->getPage())
            ->limit($trackingMensajeriaTO->getLimit())
            ->get();
        $logMessage .= 'Termina guias filtardas comercio:' . Auth::user()->comercio_id .PHP_EOL;
        
        $logMessage .= 'Lista guias regresacomercio:' . Auth::user()->comercio_id .PHP_EOL;
        $logMessage .= $guias .PHP_EOL;
        Log::info($logMessage);
        return ['total' => $total,'data'=>$guias->toArray()];

        //return $guias;
    }

    
    public function totalGuiasPorFecha(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        //guias de la grafica de guias por dia y mensajeria
        $guiasMensajerias = GuiaMensajeria::select('mensajerias.clave',
            DB::raw('DATE(guias_mensajerias.created_at) as fecha'),
            'mensajeria_id',
            DB::raw('COUNT(1) as count')
        )
        ->join('mensajerias', 'mensajerias.id', '=', 'guias_mensajerias.mensajeria_id')
        ->where('guias_mensajerias.created_at', '>=', "{$guiaMensajeriaTO->getFechaInicio()} 00:00:00")
        ->where('guias_mensajerias.created_at', '<=', "{$guiaMensajeriaTO->getFechaFin()} 23:59:59")
        ->whereNotIn('status_entrega', [3, 5])
        ->where('guias_mensajerias.estatus', '!=', 2)
        ->groupBy('mensajeria_id', DB::raw('DAY(guias_mensajerias.created_at)'))
        ->get();

        

        //die(print_r($guiasMensajerias->toArray()));
        
        return $guiasMensajerias;
    }

    private function guardarProductoCotizacion(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
        $productos = [];
        foreach($bitacoraCotizacionMensajeriaTO->getProductos() as $producto){
            $productos[] = [
                'id_bitacora_cotizacion' => $bitacoraCotizacionMensajeriaTO->getId(),
                'descripcion_sat' => $producto['descripcion_sat'],
                'codigo_sat' => $producto['codigo_sat'],
                'peso' => $producto['peso'],
                'largo' => $producto['largo'],
                'ancho' => $producto['ancho'],
                'alto' => $producto['alto'],
                'precio' => $producto['precio']
            ];

        }
        $productosCotizaciones = ProductoCotizacion::insert($productos);
    }

    private function guardarPaquetesCotizacion(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
        Log::info('En guardarProductoCotizacion');
        $paquetes = [];
        foreach($bitacoraCotizacionMensajeriaTO->getPaquetesDetalle() as $paquete){
            $paquetes[] = [
                'id_bitacora_cotizacion' => $bitacoraCotizacionMensajeriaTO->getId(),
                'peso' => $paquete['peso'],
                'largo' => $paquete['largo'],
                'ancho' => $paquete['ancho'],
                'alto' => $paquete['alto']
            ];

        }
        PaqueteCotizacion::insert($paquetes);
    }


}
