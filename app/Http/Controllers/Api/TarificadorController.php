<?php

namespace App\Http\Controllers\Api;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;
use App\ClaroEnvios\Mensajerias\CartaPorte\CartaPorteT0;
use App\ClaroEnvios\Mensajerias\CostoMensajeria;
use App\ClaroEnvios\Mensajerias\GuiaMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Mensajeria;
use App\ClaroEnvios\Mensajerias\MensajeriaCotizable;
use App\ClaroEnvios\Mensajerias\MensajeriaRepository;
use App\ClaroEnvios\Mensajerias\MensajeriaServiceInterface;
use App\ClaroEnvios\Mensajerias\MensajeriaTO;
use App\ClaroEnvios\Mensajerias\MensajeriaValidacion;
use App\ClaroEnvios\Mensajerias\ServicioMensajeria;
use App\ClaroEnvios\Negociacion\Negociacion;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Sepomex\CodigoPostalZona;
use App\ClaroEnvios\Sepomex\Sepomex;
use App\ClaroEnvios\Sepomex\SepomexTO;
use App\ClaroEnvios\TabuladoresMensajerias\Tabulador;
use App\ClaroEnvios\TabuladoresMensajerias\TabuladorMensajeria;
use App\ClaroEnvios\TabuladoresMensajerias\TarifaMensajeriaZona;
use App\Events\LogHttpRequest;
use App\Exceptions\ValidacionException;
use App\Http\Requests\Tarificador\CotizarTarificadorRequest;
use GuzzleHttp\Exception\ClientException;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;
use App\ClaroEnvios\TabuladoresMensajerias\CoberturaCPMensajeria;
use App\ClaroEnvios\Mensajerias\MensajeriaQuality;
use Illuminate\Validation\ValidationException;

/**
 * Class TarificadorController
 * @package App\Http\Controllers\Api\Tarificador
 */
class TarificadorController extends Controller
{
    /**
     * @var MensajeriaServiceInterface
     */
    private $mensajeriaService;
    /**
     * @var MensajeriaValidacion
     */
    private $mensajeriaValidacion;

    private $mensajeriaRepository;


    /**
     * TarificadorController constructor.
     * @param MensajeriaServiceInterface $mensajeriaService
     * @param MensajeriaValidacion $mensajeriaValidacion
     */
    public function __construct(MensajeriaServiceInterface $mensajeriaService, MensajeriaValidacion $mensajeriaValidacion,MensajeriaRepository $repository) {
        $this->mensajeriaService = $mensajeriaService;
        $this->mensajeriaValidacion = $mensajeriaValidacion;
        $this->mensajeriaRepository = $repository;
    }


    /**
     * Busca la cotizacion consumiendo api de cada mensajeria
     * @param CotizarTarificadorRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cotizarMensajerias(CotizarTarificadorRequest $request)
    {
        $usuario = auth()->user();
        $json['success'] = true;
        $json['message'] = Response::$messages['successfulSearch'];
        $code = 200;
        $logMessage = '--------------------Inicia cotizarMensajerias-------------------'. PHP_EOL;
        try {            
            $mensajerias = Mensajeria::whereNotIn('clave',['TOOKAN','UBER'])->get();
            $comercioId = $usuario->comercio_id;
            $comercio = Comercio::findOrFail($comercioId);
            $comercioIdCosto = Auth::user()->hasRole('superadministrador')?1:$usuario->comercio_id;
            $tipoConfiguracion = $comercio->id_configuracion;

            if($tipoConfiguracion == 4){
                //throw new ValidacionException("Con figuración de comercio no permite cotizaciones");
            }
            
            $mensajeriasIds = $mensajerias->pluck('id','id');
            $negociaciones = collect();

            if (in_array($tipoConfiguracion,[2,5,6,9])) {
                $mensajeriasCostos = $mensajerias;
            } else {
                $mensajeriasCostos = $this->obtenerCostos($comercioIdCosto, $mensajeriasIds);
                $negociaciones = Negociacion::all();
            }
            //die(print_r($mensajeriasCostos->toArray()));
            $seguro = $request->input('seguro', 0);
            $tarificadorCollect = collect();
            $tarificadoresTabulador = collect();
            $tarificadoresTabuladorQuality = '';
            $tarificadoresCustom = collect();

            $pesoVolumetrico = round(($request->largo * $request->ancho * $request->alto) / 5000);
            if ($pesoVolumetrico == 0) $pesoVolumetrico = 1;

            if ($request->peso > $pesoVolumetrico) {
                $pesoCalculado = $request->peso;
            } else {
                $pesoCalculado = $pesoVolumetrico;
            }

            $logMessage .= 'Peso: ' . $request->peso . PHP_EOL;
            $logMessage .= 'Peso volumetrico: ' . $pesoVolumetrico . PHP_EOL;
            $logMessage .= 'Peso tomado: ' . $pesoCalculado . PHP_EOL;

            //busca los cp en tabla sepomex si no encuentra alguno lo inserta
            $sepomex = new Sepomex();
            if(!$sepomex->buscarCP($request->codigo_postal_origen)){
                //Log::info('No encontro cp origen en sepomex');
                $this->nuevoCp($request,$request->codigo_postal_origen);
            }

            if (!$sepomex->buscarCP($request->codigo_postal_destino)){
                //Log::info('No encontro cp destino en sepomex');
                $this->nuevoCp($request,$request->codigo_postal_destino);
            }

            $comerciosSat = Comercio::whereIn('clave',['FEDEX','UPS','EXPRESS'])->pluck('id')->toArray();

            foreach ($mensajeriasCostos as $mensajeriaCosto) {
                if (!isset($mensajeriaCosto->mensajeria_id)) {
                    $mensajeriaCosto->mensajeria_id = $mensajeriaCosto->id;
                    $mensajeriaCosto->comercio_id = $comercio->id;
                   // $mensajeriaCosto->negociacion_id = $tipoNegociacion;
                }

                if (in_array($mensajeriaCosto->mensajeria_id, $comerciosSat)) {
                    $messages = ['productos.required'=>'El campo productos es obligatorio',
                    'productos.*.required'=>'El campo :attribute es obligatorio.'];
                    

                    $validator = Validator::make($request->all(), [
                        'productos' => 'sometimes|array',
                        'productos.*' => 'required|array',
                        'productos.*.descripcion_sat' => 'required|max:150',
                        'productos.*.codigo_sat' => 'required',
                        'productos.*.peso' => 'required|integer',
                        'productos.*.largo' => 'required|integer',
                        'productos.*.ancho' => 'required|integer',
                        'productos.*.alto' => 'required|integer',
                        'productos.*.precio' => [
                            'required',
                            'regex:/^\d+(\.\d{1,2})?$/'
                        ]
                        ],$messages);

                        if ($validator->fails()) {
                            //Si la validación falla, lanzar una excepción ValidationException con los mensajes de error personalizados
                            throw new ValidationException($validator);
                        }
                }
                
                $mensajeria = Mensajeria::findOrFail($mensajeriaCosto->mensajeria_id);
                $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request, $pesoCalculado);
                $negociacionQuery = $negociaciones->where('id', $mensajeriaCosto->negociacion_id)->first();
                $negociacion = $negociacionQuery ? $negociacionQuery->clave : '';
                $mensajeriaTO->setNegociacion($negociacion);
                $mensajeriaTO->setIdConfiguracion($comercio->id_configuracion);
                //$mensajeriaTO->setTabulador($mensajeria->clave == 'QUALITY' ? true :false);
                $guiaMensajeriaTO = new GuiaMensajeriaTO();
            
                if(array_key_exists('productos',$request->all())){
                
                    $cartaPorteT0 = new CartaPorteT0();
                    $cartaPorteT0->setProductos($request->productos);
                    $guiaMensajeriaTO->setCartaPorteTO($cartaPorteT0);
                    
                }
            
                if(array_key_exists('paquetes',$request->all())){
                    $mensajeriaTO->setPaquetes($request->paquetes);
                }

                if(array_key_exists('paquetes_detalle',$request->all())){
                    $mensajeriaTO->setPaquetesDetalle($request->paquetes_detalle);
                }

                $logMessage .= 'Mensajeria: ' . $mensajeria->clave . PHP_EOL;
                $logMessage .= 'Negociacion: ' . $negociacion . PHP_EOL;
                $logMessage .= 'Configuracion: ' . $tipoConfiguracion . PHP_EOL;

                if (class_exists($mensajeria->clase)) {
                    $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);

                    if ($mensajeriaEmpresa instanceof MensajeriaCotizable) {

                        $logMessage .=  'Tafificador API' .PHP_EOL;
                        if ($mensajeria->clave == 'QUALITY') {
                            $logMessage .= 'Tafificador QUALITY Tabulador' .PHP_EOL;
                            $tarificadoresTabuladorQuality = $mensajeriaCosto;
                            //die(print_r($mensajeriaTO));
                        }

                        $tarificador = $mensajeriaEmpresa->responseTarificador($mensajeria, $seguro, $comercioId);
                        $tarificador->cotizacion = $mensajeriaEmpresa->rate(true);

                        if (isset($tarificador->cotizacion->success)){
                            if(array_key_exists('paquetes',$request->all()) && $request->paquetes > 1){ //VALIDA SI ES MUTIGUIA DHL
                                if($mensajeria->clave == 'DHL' || $mensajeria->clave == 'FEDEX'){ //si no es fedex o dhl no se muestra
                                    $tarificadorCollect->push($tarificador);
                                }
                            }else{
                                $tarificadorCollect->push($tarificador);
                            }
                        }

                    } else {
                        $logMessage .= "No es cotizable" . PHP_EOL;
                    }
                } else {
                    $logMessage .= "No existe la clase ". $mensajeria->clase . PHP_EOL;
                }
            }

            if ($tarificadoresTabulador->count() > 0) {
                $tarificadorTabulador = $this->obtenerTarificadorTabulador($tarificadoresTabulador, $request);

                $tarificadorCollect = $tarificadorCollect->merge($tarificadorTabulador);
            }

            if ($tarificadoresCustom->count() > 0) {

                $tarificadorCustom = $this->obtenerTarificadorCustom($tarificadoresCustom, $request);
                $tarificadorCollect = $tarificadorCollect->merge($tarificadorCustom);
            }

            if ($tarificadoresTabuladorQuality != '') {
                
                $tarificadorTabulador = $this->obtenerTarificadorTabuladorQuality($tarificadoresTabuladorQuality, $request, $comercio->id_configuracion);
                $tarificadorCollect = $tarificadorCollect->merge($tarificadorTabulador);
            }

            $productos = $request->productos;

            //die(print_r($tarificadorCollect));
            $this->mensajeriaService->guardarTarificadorCotizaciones($tarificadorCollect, $mensajeriaTO, $productos);
            
            //die(print_r($tarificadorCollect));
            $json['detail'] = $tarificadorCollect;
        } catch (ValidacionException $error) {
            Log::error("cotizarMensajerias ValidacionException : ".$error->getMessage() . ' ' . $error->getFile() . ': ' . $error->getLine());
            $json['success'] = false;
            $json['message'] = Response::$messages['processError'];
            $json['error'] = $error->getMessage();
            $code = $error->getCode();
        } catch (ClientException $error) {
            Log::error("cotizarMensajerias ClientException : ".$error->getMessage() . ' ' . $error->getFile() . ': ' . $error->getLine());
            $json['success'] = false;
            $json['message'] = Response::$messages['processError'];
            $json['error'] = 'Error en la busqueda de los porcentajes. ' . $error->getMessage();
            $code = $error->getCode();
        } catch (\SoapFault $error) {
            $json['success'] = false;
            $json['message'] = Response::$messages['processError'];
            $json['error'] = 'Verificar la conexion de internet. ' . $error->getMessage();
            $code = $error->getCode();
            Log::error("cotizarMensajerias SoapFault : ".$error->getMessage() . ' ' . $error->getFile() . ': ' . $error->getCode());
        } catch (\Exception $exception) {
            Log::error("cotizarMensajerias Exception : ".$exception->getMessage() . ' ' . $exception->getFile() . ': ' . $exception->getLine());
            $json['success'] = false;
            $json['message'] = Response::$messages['processError'];
            $json['error'] = $exception->getMessage();
            $code = $exception->getCode();
        }

        //$this->guardaLog($request,$json,$code);
        //Guarda log request
        event(new LogHttpRequest($request, $json, $code));
        $logMessage .= '--------------------Termina proceso cotizarMensajerias --------------------' . PHP_EOL;
        Log::info($logMessage);
        return response()->json($json);
    }


    private function obtenerTarificadorTabulador($mensajeriasTarificadores,$request){
        $tarifasMensajerias = $this->obtenerTabulador($mensajeriasTarificadores,$request);
        $mensajerias = Mensajeria::all();
        $tarificadorCollect = collect();
        $tarifasBajas = collect();

        foreach ($tarifasMensajerias as $id=>$tarifaMensajeria) {
            $mensajeriaCosto =  $mensajeriasTarificadores->where('mensajeria_id',$id)->first();
            $mensajeriaCosto->valor_paquete = $request->valor_paquete;
            $mensajeriaCosto->seguro = $request->seguro;

            $tarifa = collect($tarifaMensajeria);
            $tarifa->transform(function ($item) use ($mensajeriaCosto) {
                $precioTabulador = $item->precio;

                $costoAdicional = 0;
                if ($mensajeriaCosto->costo != 0) {
                    $costoAdicional = $mensajeriaCosto->costo;
                }elseif ($mensajeriaCosto->porcentaje != 0){
                    $costoAdicional = round($precioTabulador*($mensajeriaCosto->porcentaje/100), 4);
                }
                //die(print_r($item->peso));
                $costoSeguro = $mensajeriaCosto->seguro ? round($mensajeriaCosto->valor_paquete*($mensajeriaCosto->porcentaje_seguro/100), 4):0;
                $costoTotalCalaculado = round($precioTabulador + $costoAdicional + $costoSeguro, 4);
                $object = $item;
                $object->peso_volumetrico = $item->peso;
                $object->costo_adicional = $costoAdicional;
                $object->costo_seguro = $costoSeguro;
                $object->costo_total = $costoTotalCalaculado;
                $object->costo_mensajeria = $precioTabulador;
                return $object;
            });

            //Busca el precio mas bajo de cada servicio de la mensajeria
            $precios = $tarifa->pluck('costo_total');
            $tarifaBaja = $tarifa->firstWhere('costo_total',$precios->min());
            $tarifasBajas->push($tarifaBaja);

        }

        foreach ($tarifasBajas->sortBy('costo_total') as $tarifaBaja){
            $mensajeriaCosto =  $mensajeriasTarificadores->where('mensajeria_id',$tarifaBaja->mensajeria_id)->first();
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request);

            $mensajeria = $mensajerias->find($tarifaBaja->mensajeria_id);
            $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);

            $tarificador = $mensajeriaEmpresa->responseTarificador($mensajeria, $request->seguro);
            $cotizacion = new stdClass();
            $cotizacion->success = true;
            $tarificador->cotizacion = $cotizacion;
            $cotizacion->servicios = new stdClass();
            $servicio = $mensajeriaEmpresa->responseService($tarifaBaja->precio,0,$tarifaBaja->costo_total,$tarifaBaja->servicio);
            $cotizacion->servicios->{$tarifaBaja->servicio->nombre} = $servicio;
            $tarificadorCollect->push($tarificador);

        }

        //die(print_r($tarifaBaja->toArray()));

        return $tarificadorCollect;
    }

        private function obtenerTarificadorTabuladorQuality($mensajeriasTarificador,$request, $idConfiguracion)
    {
        $logMessage = "-------------------- Inicia obtenerTarificadorTabuladorQuality -------------------" . PHP_EOL;

        $pesoVolumetrico = round(($request->largo * $request->ancho * $request->alto)/5000) ;
        if($pesoVolumetrico == 0) $pesoVolumetrico = 1;

        $coberturaMensajeria = CoberturaCPMensajeria::where('id_mensajeria',$mensajeriasTarificador->mensajeria_id)
        ->whereIn('codigo_postal',[$request->codigo_postal_origen, $request->codigo_postal_destino])
        ->get();
        
        $coberturaOrigen = $coberturaMensajeria->where('codigo_postal',$request->codigo_postal_origen)->first();
        $coberturaDestino = $coberturaMensajeria->where('codigo_postal',$request->codigo_postal_destino)->first();

        if($coberturaDestino->estado == 'México' && $coberturaOrigen->estado == 'Ciudad de México'){
            $cobertura = 'local';
        }
        elseif($coberturaOrigen->estado ==  $coberturaDestino->estado){
            $cobertura = 'local';
        }
        else{
            $cobertura = 'foraneo';
        }

        $peso = $request->peso > $pesoVolumetrico ? $request->peso : $pesoVolumetrico;
        
        $tarifaMensajeria = TarifaMensajeriaZona::where('kg',$peso)
        ->where('zona_estado', utf8_decode($coberturaDestino->estado))
        ->where('tipo_cobertura', $cobertura)
        ->first();

        $tarificadorCollect = collect();
        $mensajeriaCosto =  $mensajeriasTarificador;

        //die(print_r($mensajeriaCosto));
        $mensajeriaCosto->valor_paquete = $request->valor_paquete;
        $mensajeriaCosto->seguro = $request->seguro;

        if ($tarifaMensajeria) {
            $precioTabulador = $tarifaMensajeria->precio;
            $costoAdicional = 0;

            if ($mensajeriaCosto->costo != 0) {
                $costoAdicional = $mensajeriaCosto->costo;
            } elseif ($mensajeriaCosto->porcentaje != 0) {
                $costoAdicional = round($precioTabulador * ($mensajeriaCosto->porcentaje / 100), 4);
            }

            if (in_array($idConfiguracion, ConfiguracionComercio::$comerciosZonas)) {
                $logMessage .= 'Calculo zonas: ' .PHP_EOL;
                $costoGuia = $precioTabulador;
                $costoTotalCalculado = round(($costoGuia / (1 - ($mensajeriaCosto->porcentaje / 100))), 2);
                $logMessage .= ' Costo guia quality: ' . $costoGuia .PHP_EOL;
                $logMessage .= ' margen: ' . $mensajeriaCosto->porcentaje .PHP_EOL;
                $logMessage .= 'Costo Total zonas: ' . $costoTotalCalculado .PHP_EOL;
            } else {
                $logMessage .= ' Calculo default: ' .PHP_EOL;
                $costoSeguro = $mensajeriaCosto->seguro ? round($mensajeriaCosto->valor_paquete * ($mensajeriaCosto->porcentaje_seguro / 100), 2) : 0;
                $logMessage .= 'margen: ' . $mensajeriaCosto->porcentaje .PHP_EOL;
                $logMessage .= ' Costo adicional calculado: ' . $costoAdicional .PHP_EOL;
                $logMessage .= ' Costo Seguro ' . $costoSeguro .PHP_EOL;
                $costoTotalCalculado = round($precioTabulador + $costoAdicional + $costoSeguro, 2);
                $logMessage .= ' Costo Total: ' . $costoTotalCalculado .PHP_EOL;
            }
            //die(print_r($item->peso));
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request);
            $mensajeriaTO->setIdConfiguracion($idConfiguracion);
            $mensajeriaEmpresa = new MensajeriaQuality($mensajeriaTO);
            $mensajeria = Mensajeria::find($mensajeriaCosto->mensajeria_id);
            $tarificador = $mensajeriaEmpresa->responseTarificador($mensajeria, $request->seguro, $mensajeriaCosto->comercio_id);

            $servicioMensajeria = ServicioMensajeria::where('mensajeria_id', $mensajeriaCosto->mensajeria_id)
            ->where('nombre', 'EXPRESS')
            ->first();
            $cotizacion = new stdClass();
            $cotizacion->success = true;
            $tarificador->tabulador = true;
            $tarificador->cotizacion = $cotizacion;
            $cotizacion->servicios = new stdClass();
            $servicio = $mensajeriaEmpresa->responseService($tarifaMensajeria->precio, $tarifaMensajeria->precio, $costoTotalCalculado, $servicioMensajeria);
            //$cotizacion->servicios = [$servicioMensajeria->nombre => $servicio];
            $cotizacion->servicios->{$servicioMensajeria->nombre} = $servicio;
            $cotizacion->request = '';
            $cotizacion->response = '';
            $cotizacion->code_response = 200;
            $tarificadorCollect->push($tarificador);
        } else {
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request);
            $mensajeriaEmpresa = new MensajeriaQuality($mensajeriaTO);
            $mensajeria = Mensajeria::find($mensajeriaCosto->mensajeria_id);
            $tarificador = $mensajeriaEmpresa->responseTarificador($mensajeria, $request->seguro, $mensajeriaCosto->comercio_id);
            $tarificador->cotizacion = new stdClass();
            $tarificador->cotizacion->success = false;
            $tarificador->cotizacion->tabulador = true;
            $tarificador->cotizacion->message = Response::$messages['noCoverage'];
            $tarificadorCollect->push($tarificador);
        }
        $logMessage .= 'Termina obtenerTarificadorTabuladorQuality' . PHP_EOL;
        Log::info($logMessage);
        //die(print_r($tarificadorCollect));
        return $tarificadorCollect;
    }


    private function obtenerTarificadorCustom($mensajeriasTarificadores,$request){
        $tabuladorT1envios = $this->obtenerTabulador($mensajeriasTarificadores,$request);
        //die(print_r($mensajeriasTarificadores));
        $zonaEnvioComercio = Sepomex::find($request->codigo_postal_origen)->zona;
        $zonaRecepcionComercio = Sepomex::find($request->codigo_postal_destino)->zona;
        $errorZona = 'No existe la zona para código postal: ';
        if(!$zonaEnvioComercio){
            throw new ValidacionException($errorZona.$this->codigo_postal_origen);
        }
        if(!$zonaRecepcionComercio){
            throw new ValidacionException($errorZona.$this->codigo_postal_destino);
        }

        $mensajerias = Mensajeria::all();
        //$tabuladorComercio = TabuladorMensajeria::where('comercio_id', Auth::user()->comercio_id)->get();
        $tabuladoresComercio = TabuladorMensajeria::where('comercio_id', 3)
            ->where('zona_envio', $zonaEnvioComercio->zona)
            ->where('zona_recepcion', $zonaRecepcionComercio->zona)
            ->where('kg', $request->peso)
            ->get();

        $tarificadorCollect = collect();

        foreach ($tabuladoresComercio as $key=>$tabulador){

            if(array_key_exists($tabulador->mensajeria_id,$tabuladorT1envios)){
                $serviciosT1Envios = collect($tabuladorT1envios[$tabulador->mensajeria_id]);
                $servicioT1envios = $serviciosT1Envios->where('mensajeria_id', $tabulador->mensajeria_id)
                    ->where('servicio.nombre',$tabulador->tipo_servicio)->first();
            }else{
                $servicioT1envios = null;
            }


            $precioComercio = $tabulador->precio;//51.200000761
            $precioT1envios = $servicioT1envios ? $servicioT1envios->precio: 0;//87.719551091
            //$precioT1envios = 65.48;
            //$precioComercio = 66.72;
            $mensajeria = $mensajerias->find($tabulador->mensajeria_id);
            $mensajeriaCosto =  $mensajeriasTarificadores->where('mensajeria_id',$tabulador->mensajeria_id)->first();
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request);
            $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);
            $costoTotal = $mensajeriaEmpresa->obtenerCostoTotalTabulador($precioComercio,$precioT1envios);

            $tarificador = $mensajeriaEmpresa->responseTarificador($mensajeria, $request->seguro);
            $cotizacion = new stdClass();
            $cotizacion->success = true;
            $tarificador->cotizacion = $cotizacion;
            $cotizacion->servicios = new stdClass();
            $servicio = $mensajeriaEmpresa->responseService($precioT1envios,$precioComercio,$costoTotal, $servicioT1envios->servicio);
            $cotizacion->servicios->{$tabulador->tipo_servicio} = $servicio;

            //die(print_r($servicioT1envios->servicio));
            $tarificadorCollect->push($tarificador);
        }
        return $tarificadorCollect;
    }

    /**
     * Obtiene tabuladores de t1envios para comercios con negociacion t1envios
     * @param $mensajeriasTarificadores
     * @param $request
     * @return array
     */
    private function obtenerTabulador($mensajeriasTarificadores, $request){
        //fedex
        $mensajeriasIdsC1 = $mensajeriasTarificadores->where('mensajeria_id',2)->pluck('mensajeria_id');

        $mensajeriasIdsC2 = $mensajeriasTarificadores->reject(function ($value) {
            return $value->mensajeria_id ==2;
        })->pluck('mensajeria_id');


        $user = Auth::user();
        $comercioId = $user->comercio_id == 1? 6 :$user->comercio_id;
        $zonasRecepcion = CodigoPostalZona::where('codigo_postal',$request->codigo_postal_destino)
            ->whereIn('mensajeria_id',$mensajeriasIdsC1)
            ->where('comercio_id',$comercioId)
            ->get();

        $pesoVolumetrico = round(($request->largo * $request->ancho * $request->alto)/5000) ;

        if($pesoVolumetrico == 0) $pesoVolumetrico = 1;

        //para fedex solo importa el destino
        $tabuladores = Tabulador::with('servicio:id,descripcion,nombre')
            ->select('tabuladores.*',DB::raw('mensajerias.descripcion as mensajeria'))
            ->join('mensajerias','tabuladores.mensajeria_id','=','mensajerias.id')
            ->whereIn('zona_recepcion', $zonasRecepcion->pluck('zona'))
            ->whereIn('tabuladores.mensajeria_id', $mensajeriasIdsC1)
            ->where('peso', $pesoVolumetrico)
            ->get();

        $tabuladoresC1 = [];//fedex
        $tabuladoresC2 = [];//dhl

        if($mensajeriasTarificadores->count() > 0) {
            $pesoVolumetrico = number_format(($request->largo * $request->ancho * $request->alto)/5000,1,'.','');
            $decimals = explode('.',$pesoVolumetrico);
            if($decimals[1] != 5){
                $pesoVolumetrico = round($pesoVolumetrico);
            }

            if($pesoVolumetrico == 0) $pesoVolumetrico = 0.5;

            $tarifasC1 = collect();
            foreach ($zonasRecepcion as $zona) {
                $tarifa = $tabuladores->where('mensajeria_id', $zona->mensajeria_id)
                    ->where('zona_recepcion', $zona->zona)
                    ->where('servicio_mensajeria_id', $zona->servicio_mensajeria_id)->first();

                if ($tarifa)
                    $tarifasC1->push($tarifa);

            }

            $tabuladoresC1[$zona->mensajeria_id] = $tarifasC1;

            $zonasEnvio = CodigoPostalZona::where('codigo_postal', $request->codigo_postal_origen)->whereIn('mensajeria_id', $mensajeriasIdsC2)
                ->where('comercio_id',$comercioId)
                ->get();
            $zonasRecepcion = CodigoPostalZona::where('codigo_postal', $request->codigo_postal_destino)->whereIn('mensajeria_id', $mensajeriasIdsC2)
                ->where('comercio_id',$comercioId)
                ->get();

            foreach ($mensajeriasIdsC2 as $mensajeria) {
                $zonaRecepcion =  $zonasRecepcion->where('mensajeria_id',$mensajeria)->first();
                $zonaEnvio =  $zonasEnvio->where('mensajeria_id',$mensajeria)->first();
                if($zonaRecepcion && $zonaEnvio ){

                    $tabulador = Tabulador::with('servicio:id,descripcion,nombre')
                        ->select('tabuladores.*', DB::raw('mensajerias.descripcion as mensajeria'))
                        ->join('mensajerias', 'tabuladores.mensajeria_id', '=', 'mensajerias.id')
                        ->where('zona_envio', $zonaEnvio->zona)
                        ->where('zona_recepcion', $zonaRecepcion->zona)
                        ->where('mensajeria_id', $mensajeria)
                        ->where('peso',$pesoVolumetrico)
                        ->get();

                    //die(print_r($tabulador->count() > 0));
                    if($tabulador->count() > 0)
                        $tabuladoresC2[$mensajeria] = $tabulador;

                }
            }
        }
        $tarifasMensajerias = $tabuladoresC1 + $tabuladoresC2;
        //die(print_r($tarifasMensajerias));

        return $tarifasMensajerias;

    }

    private function setDatosMensajeria($mensajeriaCosto, $request, $pesoCalculado = null){
        $mensajeriaTO = new MensajeriaTO($request->all());
        $mensajeriaTO->buscarSiglasSepomex();
        //$usuario = auth()->user();
        $mensajeriaTO->setComercio($mensajeriaCosto->comercio_id);
        $mensajeriaTO->setId($mensajeriaCosto->mensajeria_id);
        $mensajeriaTO->setPorcentaje($mensajeriaCosto->porcentaje);
        $mensajeriaTO->setCosto($mensajeriaCosto->costo);
        $mensajeriaTO->setCostoSeguro($mensajeriaCosto->costo_seguro);
        $mensajeriaTO->setCostoAdicional($mensajeriaCosto->costo_adicional);
        $mensajeriaTO->setNegociacionId($mensajeriaCosto->negociacion_id);
        $mensajeriaTO->setPorcentajeSeguro($mensajeriaCosto->porcentaje_seguro);
        $mensajeriaTO->setTabulador($request->tabulador);
        $mensajeriaTO->setPesoCalculado($pesoCalculado);
        //die(print_r($mensajeriaTO));

        return $mensajeriaTO;
    }

    /**
     * @param $comercioId
     * @return \Illuminate\Support\Collection
     */
    private function obtenerCostos($comercioId, $mensajeriasIds){
        $mensajeriasCostos = CostoMensajeria::where('comercio_id',$comercioId)->whereIn('mensajeria_id',$mensajeriasIds)
            ->where('is_deleted',0)->get();
        //die("<pre>".print_r($mensajeriasCostos->toArray()));
        //die("<pre>".print_r($comercioId));
        if($mensajeriasCostos->count() == 0){
            throw new  \Exception( 'No cuenta con negociacion configurada');
        }

        return $mensajeriasCostos;
    }
    private function nuevoCP($request, $nuevoCp){
        $sepomexTO = new SepomexTO();
        $sepomexTO->setDCodigo($nuevoCp);
        $sepomex = new Sepomex();
        $sepomex->nuevo($sepomexTO);
    }

}
