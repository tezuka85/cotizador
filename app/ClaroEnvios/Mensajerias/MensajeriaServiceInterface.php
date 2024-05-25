<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Configuracion\ConfiguracionMensajeriaUsuarioTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponseTO;
use Illuminate\Support\Collection;

/**
 * Interface MensajeriaServiceInterface
 * @package App\ClaroEnvios\Mensajerias
 */
interface MensajeriaServiceInterface
{

    /**
     * Metodo que busca las mensajerias en la base de datos de acuerdo a los parametros pasados
     * @param MensajeriaTO $mensajeriaTO
     * @return mixed
     */
    public function buscarMensajerias(MensajeriaTO $mensajeriaTO);

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
    );

    /**
     * Metodo que guarda la bitacoraCotizacionMensajeria a partir de la respuesta de la cotizacion
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed
     */
    public function guardarBitacoraCotizacionMensajeria(
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
    );

    /**
     * Metodo que guarda la guia de la mensajeria junto con sus tablas anidadas
     * como son los origenes y destinos
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     */
    public function guardarGuiMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null);

    /**
     * Metodo que guarda la guia mensajeria de acuerdo a los datos mandados por el TO
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     */
    public function buscarGuiaMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO);

    /**
     * Metodo que busca la guia de la mensajeria en su api
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     * @throws \App\Exceptions\ValidacionException
     */
    public function buscarMensajeriaGuiaApi(GuiaMensajeriaTO $guiaMensajeriaTO);

    /**
     * Metodo que busca la cotizacion por el id
     * @param BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
     * @return mixed
     */
    public function findBitacoraCotizacionMensajeria(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO);

    public function findBitacoraCotizacionMensajeriaByToken(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO);

    /**
     * Metodo que busca la mensajeria por el id
     * @param MensajeriaTO $mensajeriaTO
     * @return mixed
     */
    public function findMensajeria(MensajeriaTO $mensajeriaTO);

    /**
     * Metodo que busca las Mensajerias de acuerdo a un arreglo de id's
     * @param $arrayMensajeriasId
     * @return mixed
     */
    public function buscarMensajeriasByIds($arrayMensajeriasId);

    /**
     * Guardado de cotizacion de mensajerias
     * @param Collection $tarificadorCollect
     * @param MensajeriaTO $mensajeriaTO
     */
    public function guardarTarificadorCotizaciones(Collection $tarificadorCollect, MensajeriaTO $mensajeriaTO, $productos = []);

    /**
     * Peticion para recoleccion
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     * @throws \App\Exceptions\ValidacionException
     */
    public function recoleccionProceso(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function recoleccionMensajeriaProceso(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO);

    public function buscarConfiguracionesMensajeriasUsuariosByIds(
        ConfiguracionMensajeriaUsuarioTO $configuracionMensajeriaUsuarioTO,
        $arrayMensajeriasId
    );

    public function configuracionFormatoGuiaImpresionMensajerias($arrayMensajeriasId);

    public function buscarGuiasMensajeriasResumen(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function buscarGuiasMensajeriasTotales(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function guardaConfiguracionLlaves(AccesoComercioMensajeriaTO $accesoComercioMensajeriaTO);

    public function buscarCotizacionesResumen($fechaInicio, $fechaFin, $mensajeriaId, $comercioId);

    public function topGuiasOrigen(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function topGuiasDestino(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function topCodigosPostalesDestino(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function topCodigosPostalesOrigen(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function topComercios(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function buscarGuiasCostos(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function detalleFacturacion(GuiaMensajeriaTO $guiaMensajeriaTO, array $params);

    public function getTotalesGuias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params);

    public function getTotalesMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, array $params = []);

    public function buscarGuiasMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function guardarGuiMensajeriaSSO(GuiaMensajeriaTO $guiaMensajeriaTO, $pgs = null);
   
    public function totalGuiasPorFecha(GuiaMensajeriaTO $guiaMensajeriaTO);

    public function guiasPorEstado(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo);

    public function guiasPorEstadoMensajerias(GuiaMensajeriaTO $guiaMensajeriaTO, $tipo,$codigoEstado);

    

}
