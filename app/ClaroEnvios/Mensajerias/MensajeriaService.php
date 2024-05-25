<?php

namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaResponseTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Configuracion\ConfiguracionMensajeriaUsuarioTO;
use App\ClaroEnvios\Mensajerias\FormatoGuiaImpresionMensajeria\FormatoGuiaImpresionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\GuiaMensajeriaRecoleccionResponseTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\GuiaMensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccion;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponseTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponse;
use App\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaService
 * @package App\ClaroEnvios\Mensajerias
 */
class MensajeriaService implements MensajeriaServiceInterface
{
    /**
     * @var MensajeriaRepositoryInterface
     */
    private $mensajeriaRepository;

    /**
     * MensajeriaService constructor.
     */
    public function __construct(
        MensajeriaRepositoryInterface $mensajeriaRepository
    ) {
        $this->mensajeriaRepository = $mensajeriaRepository;
    }

    /**
     * Metodo que busca las mensajerias en la base de datos de acuerdo a los parametros pasados
     * @param MensajeriaTO $mensajeriaTO
     * @return mixed
     */
    public function buscarMensajerias(MensajeriaTO $mensajeriaTO)
    {
        return $this->mensajeriaRepository->buscarMensajerias($mensajeriaTO);
    }

    /**
     * Busca los costos de mensajerias porcentajes de acuerdo a los parametros pasados en el TO
     * y por el arreglo de mensajeria_id como parametro opcional
     * @param CostoMensajeriaTO $costoMensajeriaTO
     * @param array $arrayMensajeriasIds
     * @return mixed
     */
    public function buscarCostosMensajeriasPorcentajes(
        CostoMensajeriaTO $costoMensajeriaTO,
        $arrayMensajeriasIds = []
    ) {
        return $this->mensajeriaRepository
            ->buscarCostosMensajeriasPorcentajes($costoMensajeriaTO, $arrayMensajeriasIds);
    }

    /**
     * Metodo que guarda la bitacoraCotizacionMensajeria a partir de la respuesta de la cotizacion
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed|void
     */
    public function guardarBitacoraCotizacionMensajeria(
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
    ) {
        $this->mensajeriaRepository->guardarBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeriaTO);
    }

    /**
     * Metodo que guarda la guia de la mensajeria junto con sus tablas anidadas
     * como son los origenes y destinos
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed|void
     */
    public function guardarGuiMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null )
    {
//        die(var_dump($pgs));
        $this->mensajeriaRepository->guardarGuiMensajeria($guiaMensajeriaTO, $pgs);
    }

    /**
     * Metodo que guarda la guia mensajeria de acuerdo a los datos mandados por el TO
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     */
    public function buscarGuiaMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaRepository->buscarGuiaMensajeria($guiaMensajeriaTO);
    }

    /**
     * Metodo que busca la guia de la mensajeria en su api
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     */
    public function buscarMensajeriaGuiaApi(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiaMensajeria = $this->mensajeriaRepository
            ->buscarGuiaMensajeria($guiaMensajeriaTO)
            ->last();

        $guiaMensajeria->load('bitacoraCotizacionMensajeria.mensajeria');
        $mensajeria = $guiaMensajeria->bitacoraCotizacionMensajeria->mensajeria;
        $mensajeriaTO = new MensajeriaTO();
        $mensajeriaTO->setCodigoPostalDestino($guiaMensajeria->bitacoraCotizacionMensajeria->codigo_postal_destino);
        $mensajeriaTO->setCodigoPostalOrigen($guiaMensajeria->bitacoraCotizacionMensajeria->codigo_postal_origen);
        $mensajeriaTO->setId($mensajeria->id);
        $mensajeriaTO->setComercio(auth()->user()->comercio_id);
        $mensajeriaTO->buscarSiglasSepomex();
        $mensajeriaTO->setNegociacionId($guiaMensajeria->bitacoraCotizacionMensajeria->negociacion_id);
        $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);
        $mensajeriaEmpresa->setGuiaMensajeria($guiaMensajeria);

        $track =  $mensajeriaEmpresa->rastreoGuia();

        if($track->getActualiza() == true){
            $trackMensajeriaResponseTO = new TrackMensajeriaResponseTO();
            $trackMensajeriaResponseTO->setGuiaMensajeriaId($guiaMensajeria->id);
            $trackMensajeriaResponseTO->setRequest($track->getRequest());
            $trackMensajeriaResponseTO->setResponse($track->getResponse());
            $trackMensajeriaResponseTO->setUsuarioId(Auth::user()->id);
            $this->mensajeriaRepository->guardarTrackMensajeriaResponse($trackMensajeriaResponseTO);
        }

        return $track->getTrack();
    }

    /**
     * Metodo que busca la cotizacion por el id
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed
     */
    public function findBitacoraCotizacionMensajeria(
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
    ) {
        return $this->mensajeriaRepository
            ->findBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeriaTO);
    }

    /**
     * Metodo que busca la cotizacion por el token
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed
     */
    public function findBitacoraCotizacionMensajeriaByToken(
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
    ) {
        return $this->mensajeriaRepository
            ->findBitacoraCotizacionMensajeriaByToken($bitacoraCotizacionMensajeriaTO);
    }

    /**
     * Metodo que busca la mensajeria por el id
     * @param MensajeriaTO $mensajeriaTO
     * @return mixed
     */
    public function findMensajeria(MensajeriaTO $mensajeriaTO)
    {
        return $this->mensajeriaRepository->findMensajeria($mensajeriaTO);
    }

    /**
     * Metodo que busca las Mensajerias de acuerdo a un arreglo de id's
     * @param $arrayMensajeriasId
     * @return mixed
     */
    public function buscarMensajeriasByIds($arrayMensajeriasId)
    {
        return $this->mensajeriaRepository->buscarMensajeriasByIds($arrayMensajeriasId);
    }

    /**
     * Guardado de cotizacion de mensajerias
     * @param Collection $tarificadorCollect
     * @param MensajeriaTO $mensajeriaTO
     */
    public function guardarTarificadorCotizaciones(Collection $tarificadorCollect, MensajeriaTO $mensajeriaTO, $productos = []) {
        $arrayBitacoraCotizacionMensajeriaTO = [];
         
        foreach ($tarificadorCollect as $tarificador) {
            $cotizacion = $tarificador->cotizacion;


            if ($cotizacion->success == true) {
                $servicios = $cotizacion->servicios;
                foreach ($servicios as $tipo_servicio => $servicio) {
                    $bitacoraCotizacionMensajeriaTO = new BitacoraCotizacionMensajeriaTO();
                    $servicio->token = &$bitacoraCotizacionMensajeriaTO->token;
                    $bitacoraCotizacionMensajeriaTO->setMensajeriaId($tarificador->id);
                    $bitacoraCotizacionMensajeriaTO->setComercioId($tarificador->comercio);
                    $bitacoraCotizacionMensajeriaTO->setSeguro($tarificador->seguro);
                    $bitacoraCotizacionMensajeriaTO->setCodigoPostalOrigen($mensajeriaTO->getCodigoPostalOrigen());
                    $bitacoraCotizacionMensajeriaTO->setCodigoPostalDestino($mensajeriaTO->getCodigoPostalDestino());
                    $bitacoraCotizacionMensajeriaTO->setDatosTarificador($servicio);
                    $bitacoraCotizacionMensajeriaTO->setDiasEmbarque($mensajeriaTO->getDiasEmbarque());
                    $bitacoraCotizacionMensajeriaTO->setUsuarioId(auth()->id());
                    $bitacoraCotizacionMensajeriaTO->setFechaLiberacion($mensajeriaTO->getFechaLiberacion());
                    $bitacoraCotizacionMensajeriaTO->setNegociacionId($servicio->negociacion_id);
                    $bitacoraCotizacionMensajeriaTO->setTipoPaquete($mensajeriaTO->getTipoPaquete());
                    $bitacoraCotizacionMensajeriaTO->setPaqueteComercio($mensajeriaTO->getPaqueteComercio());
                    $bitacoraCotizacionMensajeriaTO->setCostoZonaExtendida($servicio->costo_zona_extendida);
                    $bitacoraCotizacionMensajeriaTO->setNumeroExterno($mensajeriaTO->getNumeroExterno());
                    $bitacoraCotizacionMensajeriaTO->setEnvioInternacional($tarificador->envio_internacional??false);
                    $bitacoraCotizacionMensajeriaTO->setMoneda($mensajeriaTO->getMoneda());
                    $bitacoraCotizacionMensajeriaTO->setPaisDestino($mensajeriaTO->getPaisDestino());
                    $bitacoraCotizacionMensajeriaTO->setIdConfiguracion($mensajeriaTO->getIdConfiguracion());
                    $bitacoraCotizacionMensajeriaTO->setPesoVolumetrico($servicio->peso_volumetrico);
                    $bitacoraCotizacionMensajeriaTO->setProductos($productos);
                    //Valida si tiene paquetes 
                    if ($mensajeriaTO->getPaquetes() != null) {
                        
                        $bitacoraCotizacionMensajeriaTO->setPaquetes($mensajeriaTO->getPaquetes());
                    }else{
                        $bitacoraCotizacionMensajeriaTO->setPaquetes(1);
                    }
                    if ($mensajeriaTO->getPaquetesDetalle() != null) {
                    
                        $bitacoraCotizacionMensajeriaTO->setPaquetesDetalle($mensajeriaTO->getPaquetesDetalle());
                    }else{
                        $bitacoraCotizacionMensajeriaTO->setPaquetesDetalle(null);
                    }


                    $bitacoraCotizacionMensajeriaTO->setTieneCotizacionTab(property_exists($tarificador,'tabulador')?true:false);
                   // $bitacoraCotizacionMensajeriaTO->setPesoVolumetrico($servicio->peso_volumetrico);
                    $bitacoraCotizacionMensajeriaResponseTO = new BitacoraCotizacionMensajeriaResponseTO();
                   
                   // die(var_dump($bitacoraCotizacionMensajeriaTO->getTieneCotizacionTab()));
                    if($servicio->negociacion_id == 2 || ($bitacoraCotizacionMensajeriaTO->getTieneCotizacionTab()==false && $mensajeriaTO->getTabulador()==false)){
                        
                        $bitacoraCotizacionMensajeriaResponseTO->setRequest($tarificador->cotizacion->request);
                        $bitacoraCotizacionMensajeriaResponseTO->setResponse($tarificador->cotizacion->response);
                        $bitacoraCotizacionMensajeriaResponseTO->setUsuarioId(auth()->id());
                        $bitacoraCotizacionMensajeriaResponseTO->setCodigoRespuesta($tarificador->cotizacion->code_response);
                        $bitacoraCotizacionMensajeriaTO->setBitacoraCotizacionMensajeriaResponseTO($bitacoraCotizacionMensajeriaResponseTO);
                    }
                  
                    if($mensajeriaTO->getTabulador() || $bitacoraCotizacionMensajeriaTO->getTieneCotizacionTab()){
                      //die(print_r($bitacoraCotizacionMensajeriaTO));
                        $bitacoraCotizacionMensajeriaTO->setTieneCotizacion(false);
                        $bitacoraCotizacionMensajeriaTO->setTieneCotizacionTab(true);

                    }         

                    $arrayBitacoraCotizacionMensajeriaTO[] = $bitacoraCotizacionMensajeriaTO;

                    if(property_exists($servicio,'negociacion')){
                        //configuracion zonas API no se muestra esta informacion
                        if(in_array($servicio->id_configuracion, [3,4])){
                            unset($cotizacion->servicios->$tipo_servicio->costo_mensajeria);
                            unset($cotizacion->servicios->$tipo_servicio->porcentaje_negociacion);
                            unset($cotizacion->servicios->$tipo_servicio->costo_negociacion);
                            unset($cotizacion->servicios->$tipo_servicio->porcentaje_seguro);
                            unset($cotizacion->servicios->$tipo_servicio->costo_seguro);
                        }
                    }   

                    unset($cotizacion->servicios->$tipo_servicio->negociacion);
                }
            }
            unset($cotizacion->request);
            unset($cotizacion->response);
        }
      // die(print_r($arrayBitacoraCotizacionMensajeriaTO));
    
        $this->mensajeriaRepository
            ->guardarArrayBitacoraCotizacionMensajeriaTO($arrayBitacoraCotizacionMensajeriaTO);
    }

    /**
     * Peticion para recoleccion
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     */
    public function recoleccionProceso(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiaMensajeria = $this->mensajeriaRepository
            ->buscarGuiaMensajeria($guiaMensajeriaTO)
            ->first();
        $guiaMensajeria->load(
            'bitacoraCotizacionMensajeria.mensajeria',
            'bitacoraMensajeriaOrigen'
        );
        $guiaMensajeriaTO->setId($guiaMensajeria->id);
        $bitacoraCotizacionMensajeria = $guiaMensajeria->bitacoraCotizacionMensajeria;
        $bitacoraMensajeriaOrigen = $guiaMensajeria->bitacoraMensajeriaOrigen;

        $bitacoraCotizacionMensajeriaTO = new BitacoraCotizacionMensajeriaTO();
        $bitacoraCotizacionMensajeriaTO->setDatosBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeria);

        $bitacoraMensajeriaOrigenTO = new BitacoraMensajeriaOrigenTO();
        $bitacoraMensajeriaOrigenTO->setDatosBitacoraMensajeriaOrigen($bitacoraMensajeriaOrigen);

        $guiaMensajeriaTO->setBitacoraCotizacionMensajeriaTO($bitacoraCotizacionMensajeriaTO);
        $guiaMensajeriaTO->setBitacoraMensajeriaOrigenTO($bitacoraMensajeriaOrigenTO);

        $mensajeria = $guiaMensajeria->bitacoraCotizacionMensajeria->mensajeria;
        $mensajeriaTO = new MensajeriaTO();
        $mensajeriaTO->setComercio(auth()->user()->comercio_id);
        $mensajeriaTO->setId($mensajeria->id);
        $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);
        if ($mensajeriaEmpresa instanceof MensajeriaCotizable) {
            $recoleccion = $mensajeriaEmpresa->recoleccion($guiaMensajeriaTO);
            if (isset($recoleccion->pick_up)) {
                $guiaMensajeriaRecoleccionTO = new GuiaMensajeriaRecoleccionTO();
                $guiaMensajeriaRecoleccionTO->setGuiaMensajeriaId($guiaMensajeriaTO->getId());
                $guiaMensajeriaRecoleccionTO->setPickUp($recoleccion->pick_up);
                $guiaMensajeriaRecoleccionTO->setUsuarioId(auth()->id());
                $guiaMensajeriaRecoleccionTO->setLocalizacion($recoleccion->localizacion);
                $guiaMensajeriaRecoleccionResponseTO = new GuiaMensajeriaRecoleccionResponseTO();
                $guiaMensajeriaRecoleccionResponseTO->setUsuarioId(auth()->id());
                $guiaMensajeriaRecoleccionResponseTO->setRequest($recoleccion->request);
                $guiaMensajeriaRecoleccionResponseTO->setResponse($recoleccion->response);
                $guiaMensajeriaRecoleccionTO
                    ->setGuiaMensajeriaRecoleccionResponseTO(
                        $guiaMensajeriaRecoleccionResponseTO
                    );
                DB::transaction(
                    function () use ($guiaMensajeriaRecoleccionTO) {
                        $this->mensajeriaRepository
                            ->guardarGuiaMensajeriaRecoleccion($guiaMensajeriaRecoleccionTO);
                    }
                );
            }
            return $recoleccion;
        }
    }

    public function recoleccionMensajeriaProceso(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        Log::info("Entra a recoleccionMensajeriaProceso mensajeriaService");
        $mensajeria = $mensajeriaRecoleccionTO->getmensajeria();

        $usuario = Auth::user();
        $mensajeriaTO = new MensajeriaTO();
        $mensajeriaTO->setComercio($usuario->comercio_id);
        $mensajeriaTO->setNegociacionId($mensajeriaRecoleccionTO->getNegociacionId());
        $mensajeriaTO->setId($mensajeriaRecoleccionTO->getmensajeria()->id);

        $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);
        $recoleccion = $mensajeriaEmpresa->recoleccionMensajeria($mensajeriaRecoleccionTO);
        $mensajeriaRecoleccionTO->setLocalizacion($recoleccion->localizacion);
        $mensajeriaRecoleccionTO->setPickUp($recoleccion->pick_up);
        $mensajeriaRecoleccionTO->setNegociacionId($mensajeriaRecoleccionTO->getNegociacionId());
        $this->mensajeriaRepository->guardarMensajeriaRecoleccion($mensajeriaRecoleccionTO);
        return $recoleccion;

    }

    public function buscarConfiguracionesMensajeriasUsuariosByIds(
        ConfiguracionMensajeriaUsuarioTO $configuracionMensajeriaUsuarioTO,
        $arrayMensajeriasId
    ) {
        return $this->mensajeriaRepository
            ->buscarConfiguracionesMensajeriasUsuariosByIds(
                $configuracionMensajeriaUsuarioTO,
                $arrayMensajeriasId
            );
    }

    public function configuracionFormatoGuiaImpresionMensajerias($arrayMensajeriasId)
    {
        $configuracionMensajeriaUsuarioTO = new ConfiguracionMensajeriaUsuarioTO();
        $configuracionMensajeriaUsuarioTO->setUsuarioId(auth()->id());
        $configuracionesMensajeriasUsuarios = $this->mensajeriaRepository
            ->buscarConfiguracionesMensajeriasUsuariosByIds(
                $configuracionMensajeriaUsuarioTO,
                $arrayMensajeriasId
            );
        $formatoGuiaImpresionMensajeriaTO = new FormatoGuiaImpresionMensajeriaTO();
        $formatoGuiaImpresionMensajeriaTO->setUsuarioId(auth()->id());
        $formatoGuiaImpresionMensajeriaTO->setDefault(1);
        $formatosGuiasImpresionMensajerias = $this->mensajeriaRepository
            ->buscarFormatosImpresionMensajerias(
                $formatoGuiaImpresionMensajeriaTO,
                $arrayMensajeriasId
            );
        $formatosGuiasImpresionMensajerias->load('formatoGuiaImpresion');
        $formatosGuiasImpresionMensajerias = $formatosGuiasImpresionMensajerias->mapWithKeys(
            function ($elemento) {
                $elemento->formato_clave = $elemento->formatoGuiaImpresion->clave;
                $elemento->formato_extension = $elemento->formatoGuiaImpresion->extension;
                return [
                    $elemento->mensajeria_id => $elemento->only(['formato_clave', 'formato_extension'])
                ];
            }
        );
        $configuracionesMensajeriasUsuarios->load('formatoGuiaImpresion');
        $configuracionesMensajeriasUsuarios = $configuracionesMensajeriasUsuarios
            ->mapWithKeys(
                function ($elemento) {
                    $elemento->formato_clave = $elemento->formatoGuiaImpresion->clave;
                    $elemento->formato_extension = $elemento->formatoGuiaImpresion->extension;
                    return [
                        $elemento->mensajeria_id => $elemento->only(['formato_clave', 'formato_extension'])
                    ];
                }
            );
        foreach ($arrayMensajeriasId as $mensajeria_id) {
            if (!$configuracionesMensajeriasUsuarios->has($mensajeria_id)) {
                $configuracionesMensajeriasUsuarios->put(
                    $mensajeria_id,
                    $formatosGuiasImpresionMensajerias->get($mensajeria_id)
                );
            }
        }
        return $configuracionesMensajeriasUsuarios;
    }

    public function buscarGuiasMensajeriasResumen(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaRepository->buscarGuiasMensajeriasResumen($guiaMensajeriaTO);
    }

    public function buscarGuiasMensajeriasTotales(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaRepository->buscarGuiasMensajeriasTotales($guiaMensajeriaTO);
    }

    public function guardaConfiguracionLlaves(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTo)
    {
        return $this->mensajeriaRepository->guardaConfiguracionLlaves($accesoComercioMensajeriaTo);
    }

    public function buscarCotizacionesResumen($fechaInicio, $fechaFin, $mensajeriaId, $comercioId)
    {
        return $this->mensajeriaRepository->buscarCotizacionesResumen($fechaInicio, $fechaFin, $mensajeriaId, $comercioId);
    }

    public function topGuiasOrigen(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaRepository->topGuiasOrigen($guiaMensajeriaTO);
    }

    public function topGuiasDestino(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaRepository->topGuiasDestino($guiaMensajeriaTO);
    }

    public function topCodigosPostalesDestino(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaRepository->topCodigosPostalesDestino($guiaMensajeriaTO);
    }

    public function topCodigosPostalesOrigen(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaRepository->topCodigosPostalesOrigen($guiaMensajeriaTO);
    }

    public function topComercios(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaRepository->topComercios($guiaMensajeriaTO);
    }

    public function buscarGuiasCostos(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaRepository->buscarGuiasCostos($guiaMensajeriaTO);
    }

    public function detalleFacturacion(GuiaMensajeriaTO $guiaMensajeriaTO, array $params){
        return $this->mensajeriaRepository->detalleFacturacion($guiaMensajeriaTO, $params);
    }

    public function getTotalesGuias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params){
        return $this->mensajeriaRepository->getTotalesGuias($guiaMensajeriaTO, $params);
    }

    public function getTotalesMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = []){
        return $this->mensajeriaRepository->getTotalesMensajerias($guiaMensajeriaTO, $params);
    }

    public function buscarGuiasMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaRepository->buscarGuiasMensajerias($guiaMensajeriaTO);
    }

    public function guardarGuiMensajeriaSSO(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null )
    {
//        die(var_dump($pgs));
        $this->mensajeriaRepository->guardarGuiMensajeriaSSO($guiaMensajeriaTO, $pgs);
    }

    public function totalGuiasPorFecha(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaRepository->totalGuiasPorFecha($guiaMensajeriaTO);
    }

    public function guiasPorEstado(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo){
        return $this->mensajeriaRepository->guiasPorEstado($guiaMensajeriaTO, $tipo);
    }

    public function guiasPorEstadoMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo,$codigoEstado){
        return $this->mensajeriaRepository->guiasPorEstadoMensajerias($guiaMensajeriaTO, $tipo,$codigoEstado);
    }
}
