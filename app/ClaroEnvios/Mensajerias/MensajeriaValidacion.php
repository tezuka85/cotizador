<?php

namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\CotizacionPaquete;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Saldos\SGeneral;
use App\ClaroEnvios\T1Paginas\ComercioPaquete;
use App\Exceptions\ValidacionException;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaValidacion
 * @package App\ClaroEnvios\Mensajerias
 */
class MensajeriaValidacion
{
    /**
     * @var MensajeriaRepositoryInterface
     */
    private $mensajeriaRepository;

    /**
     * MensajeriaValidacion constructor.
     */
    public function __construct(
        MensajeriaRepositoryInterface $mensajeriaRepository
    ) {
        $this->mensajeriaRepository = $mensajeriaRepository;
    }

    /**
     * Valida que la clave exista en las guias
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @throws ValidacionException
     */
    public function guiaClaveExistente(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiaMensajeria = $this->mensajeriaRepository->buscarGuiaMensajeria($guiaMensajeriaTO);
        if (is_null($guiaMensajeriaTO->getGuia()) || $guiaMensajeria->count() == 0) {
            throw new ValidacionException('La guia '.$guiaMensajeriaTO->getGuia().' no existe');
        }
    }

    /**
     * Valida que el id de la mensajeria exista
     * @param $mensajeria
     * @throws ValidacionException
     */
    public function existenteMensajeria($mensajeria)
    {
        if (!isset($mensajeria->id)) {
            throw new ValidacionException('La mensajeria no existe');
        }
    }

    /**
     * Valida que el id de la cotizacion sea una existente
     * @param $bitacoraCotizacionMensajeria
     * @throws ValidacionException
     */
    public function existenteBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeria)
    {
        if (!isset($bitacoraCotizacionMensajeria->id)) {
            throw new ValidacionException('La cotizacion no existe');
        }
    }

    public function validarBitacoraCotizacionMensajeriaGuiaConfigurada(
        GuiaMensajeriaTO $guiaMensajeriaTO
    ) {
        $guiaMensajeriaValidacionTO = new GuiaMensajeriaTO();
        $guiaMensajeriaValidacionTO
            ->setBitacoraCotizacionMensajeriaId(
                $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId()
            );
        $guiasMensajerias = $this->mensajeriaRepository
            ->buscarGuiaMensajeria($guiaMensajeriaValidacionTO);
        if ($guiasMensajerias->count()) {
            Log::error('La cotizacion ya tiene guia generada');
            throw new ValidacionException('La cotizacion ya tiene guia generada');
        }
    }

    public function validarBitacoraCotizacionUsuarioComercio(BitacoraCotizacionMensajeriaTO $bitacoraCotizacion, $usuarioId, $comercioId) {

        if ($bitacoraCotizacion->getUsuarioId() != $usuarioId) {
            throw new ValidacionException('La cotizacion pertenece a otro usuario');
        }

        if ($bitacoraCotizacion->getComercioId() != $comercioId) {
            Log::info('bitacora comercio: '.$bitacoraCotizacion->getComercioId().' / '. $comercioId);
            throw new ValidacionException('La cotizacion pertenece a otro comercio');
        }
    }

    public function pickUpNoGenerado(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $guiaMensajeria = $this->mensajeriaRepository
            ->buscarGuiaMensajeria($guiaMensajeriaTO)
            ->first();

        $guiaMensajeriaRecoleccion = $guiaMensajeria->guiaMensajeriaRecoleccion;
        if (isset($guiaMensajeriaRecoleccion->id)) {
            throw new ValidacionException(
                'Ya existe un pick up: '. $guiaMensajeriaRecoleccion->pick_up
            );
        }
    }

    public function verificarCotizacionMaximoDias(
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO
    ) {
        $diasMaximo = 30;
        $bitacoraCotizacionMensajeria = $this->mensajeriaRepository
            ->findBitacoraCotizacionMensajeria($bitacoraCotizacionMensajeriaTO);
        $fechaActual = new Carbon();
        $fechaCotizacion = new Carbon($bitacoraCotizacionMensajeria->fecha_cotizacion);
        $diferenciaDias = $fechaActual->diffInDays($bitacoraCotizacionMensajeria->fecha_cotizacion);
        //die("<pre>".print_r($bitacoraCotizacionMensajeria->fecha_cotizacion));
        if ($diferenciaDias > $diasMaximo) {
            throw new ValidacionException(
                "La cotizacion tiena mas de {$diasMaximo} dias por lo cual no se puede utilizar"
                .", genere una nueva cotizacion"
            );
        }
    }

    public function rangoFechasCorrecto($fechaInicio, $fechaFin)
    {
        $fechaInicio = new Carbon($fechaInicio);
        $fechaFin = new Carbon($fechaFin);
        if ($fechaFin->lessThan($fechaInicio)) {
            throw new ValidacionException('Rango de fechas incorrecto');
        }
    }

    public function verificarMaximoDias($fechaInicio, $fechaFin, $diasMaximo = 30)
    {
        $fechaActual1 = new Carbon();
        $fechaActual2 = new Carbon();

        $diasMes = ($fechaActual2->startOfMonth()->diffInDays($fechaActual1->endOfMonth())) + 1;
        // die(print_r($diasMaximo));

        $diferenciaDias = $fechaInicio->diffInDays($fechaFin);
        $diasMaximo = $diasMaximo ?? $diasMes;

        if ($diferenciaDias > $diasMaximo) {
            throw new ValidacionException(
                "El rango maximo de dias buscado sobrepasa el permitido de $diasMaximo dias"
            );
        }
    }

    public function verificarMaximo6m($fechaInicio, $fechaFin)
    {
        $diferenciaDias = $fechaInicio->diffInDays($fechaFin);
        $fechaInicio2 = new Carbon($fechaFin->format('Y-m-d H:i:s'));
        $fechaInicio2->subMonth(6);
        $diasMaximo = $fechaInicio2->diffInDays($fechaFin);
        
        if ($diferenciaDias > $diasMaximo) {
            throw new ValidacionException(
                "El rango maximo de dias buscado sobrepasa el permitido de $diasMaximo dias"
            );
        }
    }


    public function recoleccionExistente(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        $recoleccion = $this->mensajeriaRepository->buscarRecoleccionExistente($mensajeriaRecoleccionTO);
        if($recoleccion){
            Log::info("Recoleccion existente: ".$recoleccion->numero_pickup);
            throw new \Exception("Existe una recolección con número ".$recoleccion->numero_pickup." para misma fecha y lugar");
        }
    }

    public function recoleccionGuiaExistente($guia)
    {
        $recoleccion = $this->mensajeriaRepository->buscarRecoleccionGuia($guia);
        if($recoleccion){
            Log::info("Recoleccion existente: ".$recoleccion->numero_pickup);
            throw new \Exception("Existe una recolección con número ".$recoleccion->numero_pickup." para la guía ".$guia);
        }
    }

    public function costoNegociacion(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        $negociacion = $this->mensajeriaRepository->buscarCostoNegociacion($mensajeriaRecoleccionTO);
//        die(print_r($negociacion));
        if(!$negociacion){
            Log::info("Sin negociacion costos mensajeria");
            throw new ValidacionException("No cuenta con negociacion para esta mensajeria");
        }

        return $negociacion;
    }

    public function validarPrecioGuia(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO) {

        $limitePrecio = $this->mensajeriaRepository->buscarLimitePrecio($bitacoraCotizacionMensajeriaTO);
//        die(print_r($limitePrecio));
        $precioGuia = $bitacoraCotizacionMensajeriaTO->getCostoTotal();
        if($limitePrecio && ($precioGuia < $limitePrecio->min || $precioGuia > $limitePrecio->max)){
            $mensaje ="El costo de la guía {$precioGuia} sobrepasa el limite configurado min:".$limitePrecio->min ." max:".$limitePrecio->max;
            Log::info($mensaje);
            throw new \Exception($mensaje);
        }
    }

    public function validarSaldo(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO) {
        Log::info('Valida saldo comercio: '.$bitacoraCotizacionMensajeriaTO->getComercioId());
        $saldo = SGeneral::where('comercio_id',$bitacoraCotizacionMensajeriaTO->getComercioId())->first();
        $precioGuia = $bitacoraCotizacionMensajeriaTO->getCostoTotal();
        if($saldo){
//            die($precioGuia.' - '.$saldo->saldo_actual);
            if($precioGuia > $saldo->saldo_actual){
                Log::info('Saldo: '.$saldo->saldo_actual);
                Log::info('Precio guia: '.$precioGuia);
                $mensaje ="No cuenta con saldo suficiente para este requerimiento";
                throw new \Exception($mensaje);
            }
        }else{
            $mensaje ="No cuenta con saldo para este requerimiento";
            Log::info($mensaje);
            throw new \Exception($mensaje);
        }

    }

    public function validarTokenActivo(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO, ServicioMensajeria $servicioMensajeria) {
        $comercioPaquete = ComercioPaquete::join('paquetes','paquetes.id_paquete','=','comercios_paquetes.id_paquete')
            ->where('comercios_paquetes.id_comercio',$bitacoraCotizacionMensajeriaTO->getComercioId())->first();

        $cotizacionPaquete = CotizacionPaquete::where('id_bitacora_cotizacion',$bitacoraCotizacionMensajeriaTO->getId())->firstOrFail();
        //die(print_r($comercioPaquete));

        if($cotizacionPaquete->estatus == 0){
            $mensaje ="Cotización desactivada, genere una nueva cotización";
            throw new \Exception($mensaje);

        }elseif($comercioPaquete){
            if($comercioPaquete->id_paquete != $cotizacionPaquete->id_paquete){
                Log::info('Paquete ha cambiado, se desactiva cotizacion');
    
                $cotizacionPaquete->estatus = 0;
                $cotizacionPaquete->save();
    
                $mensaje ="Cotización no corresponde a paquete actual, genere una nueva cotización";
                throw new \Exception($mensaje);
            }
        }


//        elseif($comercioPaquete->id_paquete == $cotizacionPaquete->id_paquete){
//
//            $tarifasMensajerias = TarifaMensajeria::where('id_mensajeria',$bitacoraCotizacionMensajeriaTO->getMensajeriaId())
//                ->where('id_servicio_mensajeria',$servicioMensajeria->id)
//                ->where('peso',$bitacoraCotizacionMensajeriaTO->getPeso())
//                ->where('id_paquete',$comercioPaquete->id_paquete)
//                ->firstOrFail();
//
//            if($tarifasMensajerias->precio != $bitacoraCotizacionMensajeriaTO->getCosto()){
//                Log::info('Precio ha cambiado, se desactiva cotizacion');
//
//                $cotizacionPaquete->estatus = 0;
//                $cotizacionPaquete->save();
//
//                $mensaje ="El precio de la cotización no corresponde al actual, genere una nueva cotización";
//                throw new \Exception($mensaje);
//            }
//
////            die(print_r($bitacoraCotizacionMensajeriaTO->getCosto()));
//
//        }
    }
    public function validarConfiguracionGuiaCotizacion(BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO,$comercio) {

        if($bitacoraCotizacionMensajeriaTO->getIdConfiguracion() != $comercio->id_configuracion){
            $mensaje ="La configuración de cotización no coincide a la actual, genere una nueva cotización";
            Log::info($mensaje);
            throw new \Exception($mensaje);
        }
    }
}
