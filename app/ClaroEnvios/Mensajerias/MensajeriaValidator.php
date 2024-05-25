<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Configuracion\ConfiguracionMensajeriaUsuarioTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponseTO;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaValidator
 * @package App\ClaroEnvios\Mensajerias
 */
class MensajeriaValidator implements MensajeriaServiceInterface
{
    /**
     * @var MensajeriaService
     */
    private $mensajeriaService;
    /**
     * @var MensajeriaValidacion
     */
    private $mensajeriaValidacion;

    /**
     * MensajeriaValidator constructor.
     */
    public function __construct(
        MensajeriaService $mensajeriaService,
        MensajeriaValidacion $mensajeriaValidacion
    ) {
        $this->mensajeriaService = $mensajeriaService;
        $this->mensajeriaValidacion = $mensajeriaValidacion;
    }

    /**
     * Metodo que busca las mensajerias en la base de datos de acuerdo a los parametros pasados
     * @param MensajeriaTO $mensajeriaTO
     * @return mixed
     */
    public function buscarMensajerias(MensajeriaTO $mensajeriaTO)
    {
        return $this->mensajeriaService->buscarMensajerias($mensajeriaTO);
    }

    /**
     * Busca los costos de mensajerias porcentajes de acuerdo a los parametros pasados en el TO
     * y por el arreglo de mensajeria_id como parametro opcional
     * @param CostoMensajeriaTO $costoMensajeriaO
     * @param array $arrayMensajeriasIds
     * @return mixed
     */
    public function buscarCostosMensajeriasPorcentajes(
        CostoMensajeriaTO $costoMensajeriaTO,
        $arrayMensajeriasIds = []
    ) {
        return $this->mensajeriaService
            ->buscarCostosMensajeriasPorcentajes($costoMensajeriaTO);
    }

    /**
     * Metodo que guarda la bitacoraCotizacionMensajeria a partir de la respuesta de la cotizacion
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed|void
     */
    public function guardarBitacoraCotizacionMensajeria(
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
    ) {
        $this->mensajeriaService->guardarBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeriaTO);
    }

    /**
     * Metodo que guarda la guia de la mensajeria junto con sus tablas anidadas
     * como son los origenes y destinos
     *
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed|void
     * @throws \App\Exceptions\ValidacionException
     */
    public function guardarGuiMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null)
    {
        $this->mensajeriaService->guardarGuiMensajeria($guiaMensajeriaTO, $pgs);
    }

    /**
     * Metodo que guarda la guia mensajeria de acuerdo a los datos mandados por el TO
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     */
    public function buscarGuiaMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaService->buscarGuiaMensajeria($guiaMensajeriaTO);
    }

    /**
     * Metodo que busca la guia de la mensajeria en su api
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     * @throws \App\Exceptions\ValidacionException
     */
    public function buscarMensajeriaGuiaApi(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $this->mensajeriaValidacion->guiaClaveExistente($guiaMensajeriaTO);
        return $this->mensajeriaService->buscarMensajeriaGuiaApi($guiaMensajeriaTO);
    }

    /**
     * Metodo que busca la cotizacion por el id
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed
     * @throws \App\Exceptions\ValidacionException
     */
    public function findBitacoraCotizacionMensajeria(
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
    ) {
        $bitacoraCotizacionMensajeria = $this->mensajeriaService
            ->findBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeriaTO);
        $this->mensajeriaValidacion
            ->existenteBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeria);
        return $bitacoraCotizacionMensajeria;
    }

    public function findBitacoraCotizacionMensajeriaByToken(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
        return $this->mensajeriaService->findBitacoraCotizacionMensajeriaByToken($bitacoraCotizacionMensajeriaTO);
    }

    /**
     * Metodo que busca la mensajeria por el id
     * @param MensajeriaTO $mensajeriaTO
     * @return mixed
     * @throws \App\Exceptions\ValidacionException
     */
    public function findMensajeria(MensajeriaTO $mensajeriaTO)
    {
        $mensajeria = $this->mensajeriaService->findMensajeria($mensajeriaTO);
        $this->mensajeriaValidacion->existenteMensajeria($mensajeria);
        return $mensajeria;
    }

    /**
     * Metodo que busca las Mensajerias de acuerdo a un arreglo de id's
     * @param $arrayMensajeriasId
     * @return mixed
     */
    public function buscarMensajeriasByIds($arrayMensajeriasId)
    {
        return $this->mensajeriaService->buscarMensajeriasByIds($arrayMensajeriasId);
    }

    /**
     * Guardado de cotizacion de mensajerias
     * @param Collection $tarificadorCollect
     * @param MensajeriaTO $mensajeriaTO
     */
    public function guardarTarificadorCotizaciones(Collection $tarificadorCollect, MensajeriaTO $mensajeriaTO, $productos = [])
    {
        $this->mensajeriaService->guardarTarificadorCotizaciones($tarificadorCollect, $mensajeriaTO, $productos);
    }

    /**
     * Peticion para recoleccion
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     * @throws \App\Exceptions\ValidacionException
     */
    public function recoleccionProceso(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $this->mensajeriaValidacion->guiaClaveExistente($guiaMensajeriaTO);
        $this->mensajeriaValidacion->pickUpNoGenerado($guiaMensajeriaTO);
        return $this->mensajeriaService->recoleccionProceso($guiaMensajeriaTO);
    }

    public function recoleccionMensajeriaProceso(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO){
        Log::info('recoleccionMensajeriaProceso mensajeriaValidator: '.json_decode($mensajeriaRecoleccionTO->getGuias()));
        $this->mensajeriaValidacion->recoleccionExistente($mensajeriaRecoleccionTO);
        if($mensajeriaRecoleccionTO->getGuias()){
            $this->mensajeriaValidacion->recoleccionGuiaExistente($mensajeriaRecoleccionTO->getGuias());
        }
       // $negociacion = $this->mensajeriaValidacion->costoNegociacion($mensajeriaRecoleccionTO);
        //$mensajeriaRecoleccionTO->setNegociacionId($negociacion->negociacion_id);
        return $this->mensajeriaService->recoleccionMensajeriaProceso($mensajeriaRecoleccionTO);
    }

    public function buscarConfiguracionesMensajeriasUsuariosByIds(
        ConfiguracionMensajeriaUsuarioTO $configuracionMensajeriaUsuarioTO,
        $arrayMensajeriasId
    ) {
        return $this->mensajeriaService
            ->buscarConfiguracionesMensajeriasUsuariosByIds(
                $configuracionMensajeriaUsuarioTO,
                $arrayMensajeriasId
            );
    }

    public function configuracionFormatoGuiaImpresionMensajerias($arrayMensajeriasId)
    {
        return $this->mensajeriaService
            ->configuracionFormatoGuiaImpresionMensajerias($arrayMensajeriasId);
    }

    public function validarDatosResumen(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $this->mensajeriaValidacion->rangoFechasCorrecto($guiaMensajeriaTO->getFechaInicio(), $guiaMensajeriaTO->getFechaFin());
    }

    public function rangoFechas($fechaInicio,$fechaFin)
    {
        $this->mensajeriaValidacion->rangoFechasCorrecto($fechaInicio,$fechaFin);
    }
    public function verificarMaximoDias($fechaInicio,$fechaFin)
    {
        $this->mensajeriaValidacion->verificarMaximoDias($fechaInicio,$fechaFin);
    }

    public function verificarMaximo6m($fechaInicio,$fechaFin)
    {
        $this->mensajeriaValidacion->verificarMaximo6m($fechaInicio,$fechaFin);
    }


    public function buscarGuiasMensajeriasResumen(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaService->buscarGuiasMensajeriasResumen($guiaMensajeriaTO);
    }

    public function buscarGuiasMensajeriasTotales(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaService->buscarGuiasMensajeriasTotales($guiaMensajeriaTO);
    }

    public function guardaConfiguracionLlaves(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTo)
    {
        return $this->mensajeriaService->guardaConfiguracionLlaves($accesoComercioMensajeriaTo);
    }

    public function validarDatosResumenCotizacion(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO)
    {
        $this->mensajeriaValidacion->rangoFechasCorrecto($bitacoraCotizacionMensajeriaTO->getFechaInicio(),
            $bitacoraCotizacionMensajeriaTO->getFechaFin());
    }

    public function buscarCotizacionesResumen($fechaInicio, $fechaFin, $mensajeriaId, $comercioId)
    {
        return $this->mensajeriaService->buscarCotizacionesResumen($fechaInicio, $fechaFin, $mensajeriaId, $comercioId);
    }

    public function topGuiasOrigen(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaService->topGuiasOrigen($guiaMensajeriaTO);
    }

    public function topGuiasDestino(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaService->topGuiasDestino($guiaMensajeriaTO);
    }

    public function topCodigosPostalesDestino(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaService->topCodigosPostalesDestino($guiaMensajeriaTO);
    }

    public function topCodigosPostalesOrigen(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaService->topCodigosPostalesOrigen($guiaMensajeriaTO);
    }

    public function topComercios(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaService->topComercios($guiaMensajeriaTO);
    }

    public function guardarTrackMensajeriaResponse(TrackMensajeriaResponseTO $trackMensajeriaResponseTO){
        return $this->mensajeriaService->guardarTrackMensajeriaResponse($trackMensajeriaResponseTO);
    }

    public function buscarGuiasCostos(GuiaMensajeriaTO $guiaMensajeriaTO){
        return $this->mensajeriaService->buscarGuiasCostos($guiaMensajeriaTO);
    }

    public function detalleFacturacion(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = []){
        return $this->mensajeriaService->detalleFacturacion($guiaMensajeriaTO, $params);
    }

    public function getTotalesGuias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = []){
        return $this->mensajeriaService->getTotalesGuias($guiaMensajeriaTO, $params);
    }

    public function getTotalesMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = []){
        return $this->mensajeriaService->getTotalesMensajerias($guiaMensajeriaTO, $params);
    }

    public function buscarGuiasMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaService->buscarGuiasMensajerias($guiaMensajeriaTO);
    }

    public function guardarGuiMensajeriaSSO(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null)
    {
        $this->mensajeriaService->guardarGuiMensajeriaSSO($guiaMensajeriaTO, $pgs);
    }

    public function totalGuiasPorFecha(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        return $this->mensajeriaService->totalGuiasPorFecha($guiaMensajeriaTO);
    }

    public function guiasPorEstado(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo){
        return $this->mensajeriaService->guiasPorEstado($guiaMensajeriaTO, $tipo);
    }

    public function guiasPorEstadoMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo,$codigoEstado){
        return $this->mensajeriaService->guiasPorEstadoMensajerias($guiaMensajeriaTO, $tipo,$codigoEstado);
    }

}
