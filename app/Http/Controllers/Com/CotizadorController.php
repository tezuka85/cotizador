<?php

namespace App\Http\Controllers\Com;


use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Comercios\ComerciosNiveles\ComercioNiveles;
use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;
use App\ClaroEnvios\Mensajerias\CostoMensajeria;
use App\ClaroEnvios\Mensajerias\CostosMensajerias\CostosSegurosRedpack;
use App\ClaroEnvios\Mensajerias\Mensajeria;
use App\ClaroEnvios\Mensajerias\MensajeriaCotizable;
use App\ClaroEnvios\Mensajerias\MensajeriaServiceInterface;
use App\ClaroEnvios\Mensajerias\AccesosComerciosDhl\AccesoComercioDhl;
use App\ClaroEnvios\Mensajerias\MensajeriaTO;
use App\ClaroEnvios\Mensajerias\MensajeriaValidacion;
use App\ClaroEnvios\Mensajerias\ServicioMensajeria;
use App\ClaroEnvios\Niveles\NivelesConfiguraciones\NivelConfiguracion;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Sepomex\Sepomex;
use App\ClaroEnvios\Sepomex\SepomexTO;
use App\ClaroEnvios\T1Paginas\ComercioPaquete;
use App\ClaroEnvios\TabuladoresMensajerias\CoberturaCPMensajeria;
use App\ClaroEnvios\TabuladoresMensajerias\TarifaMensajeria;
use App\ClaroEnvios\TabuladoresMensajerias\TarifaMensajeriaComercio;
use App\Events\LogHttpRequest;
use App\Exceptions\ValidacionException;
use App\Http\Requests\Tarificador\CotizarTarificadorRequest;
use App\User;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use stdClass;


/**
 * Class TarificadorController
 * @package App\Http\Controllers\Api
 */
class CotizadorController extends Controller
{
    /**
     * @var MensajeriaServiceInterface
     */
    private $mensajeriaService;
    /**
     * @var MensajeriaValidacion
     */
    private $mensajeriaValidacion;

    /**
     * CotizadorController constructor.
     * @param MensajeriaServiceInterface $mensajeriaService
     * @param MensajeriaValidacion $mensajeriaValidacion
     */
    public function __construct(MensajeriaServiceInterface $mensajeriaService, MensajeriaValidacion $mensajeriaValidacion) {
        $this->mensajeriaService = $mensajeriaService;
        $this->mensajeriaValidacion = $mensajeriaValidacion;
    }


    /**
     * Cotizacion con tablas prepago
     * @param CotizarTarificadorRequest $request
     * @return \Illuminate\Http\JsonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function cotizarMensajerias(CotizarTarificadorRequest $request)
    {
        $json['success'] = true;
        $json['message'] = Response::$messages['successfulSearch'];
        $code = 200;
        $groupLog = '--------------------Inicia Cotizacion web t1envios/t1paginas-------------------'. PHP_EOL;
        try {
            $comercio = Comercio::withTrashed()->where('clave',$request->comercio_id)->first();
            $groupLog .= 'comercio : '.$comercio->id.'-'.$comercio->clave.'-------------------'.PHP_EOL;
            $mensajerias = Mensajeria::whereNotIn('clave',['TOOKAN','UBER','QUALITY','CARGAMOS','IMILE','YANGO','BIGSMART','CAMINANDO',
            'LOGIFY','AMPM'])->get();
            $comercioUsuario =  $this->buscarComercioUsuario($comercio->id);
            $mensajeriasIds = $mensajerias->pluck('id','id');

            if((in_array($comercio->id_configuracion,ConfiguracionComercio::$comerciosPrepago))){
                $groupLog.="Comercio prepago con llaves t1envios, ".$comercio->id.'-'.$comercio->clave.PHP_EOL;
                $mensajeriasCostos = $mensajerias;
            }else{
                $groupLog.="Comercio zonas WEB con porcentajes, ".$comercio->id.'-'.$comercio->clave.PHP_EOL;
                $mensajeriasCostos = $this->obtenerCostos($comercioUsuario->comercio_id,$mensajeriasIds );
            }


            $groupLog.="Validaciones:".PHP_EOL;
            $this->validaciones($request,$comercioUsuario,$mensajeriasCostos);
            if (in_array($comercio->id_configuracion, ConfiguracionComercio::$comerciosPrepago)) {
                $comercioPaquete = ComercioPaquete::join('paquetes', 'paquetes.id_paquete', '=', 'comercios_paquetes.id_paquete')
                ->where('comercios_paquetes.id_comercio', $comercio->id)->first();
               // die(print_r( $comercio->id_configuracion));

                if ($comercioPaquete){
                    $paquete = $comercioPaquete->nombre;
                    $groupLog.='Paquete: ' . $paquete.PHP_EOL;
                }else{
                    $paquete ='White';
                    $groupLog.='Paquete: ' . $paquete.PHP_EOL;
                }

            } else {

                $comercioPaquete = null;
                $paquete = null;
            }

            $seguro = $request->input('seguro', 0);
            $pesoVolumetrico = round(($request->largo * $request->ancho * $request->alto)/5000) ;

            if ($pesoVolumetrico == 0) $pesoVolumetrico = 1;

            if ($request->peso > $pesoVolumetrico) {
                $peso = $request->peso;
            } else {
                $peso = $pesoVolumetrico;
            }

            $groupLog.="Peso tomado: " . $peso.PHP_EOL;

            if($request->envio_internacional == false){
                $groupLog.='tarificadorCollect: cotizacionNacional' .PHP_EOL;
                $tarificadorCollect =  $this->cotizacionNacional($request,$seguro,$mensajeriasCostos,$comercio,$comercioPaquete,$comercioUsuario,
                $mensajeriasIds,$paquete,$peso);

            }else{
                $groupLog.='tarificadorCollect: cotizacionInternacional' .PHP_EOL;
                $tarificadorCollect =  $this->cotizacionInternacional($request,$seguro,$mensajeriasCostos,$comercio,$comercioPaquete
                ,$comercioUsuario,$peso);
            }

            $json['detail'] = $tarificadorCollect;
        } catch (ValidacionException $error) {
            Log::error("Error en validacion - cotizarMensajerias:".$error->getMessage().' '.$error->getFile().': '.$error->getLine());
            $json['success'] = false;
            $json['message'] = Response::$messages['processError'];
            $json['error'] = $error->getMessage();
            $code = $error->getCode();
        }catch (\Exception $exception){
            Log::error("Error Exception - cotizarMensajerias:".$exception->getMessage(). ' '.$exception->getFile().': '.$exception->getLine());
            $json['success'] = false;
            $json['message'] = Response::$messages['processError'];
            $json['error'] = $exception->getMessage();
            $code = $exception->getCode();
        }

        //die(print_r($json));
        //Guarda log request
        event(new LogHttpRequest($request, $json,$code));
        $groupLog .= '--------------------Termina proceso Cotizacion web t1envios/t1paginas --------------------' . PHP_EOL;
        Log::info($groupLog);

        return response()->json($json);
    }

    private function cotizacionNacional($request,$seguro,$mensajeriasCostos,$comercio,$comercioPaquete,$comercioUsuario,$mensajeriasIds,$paquete,$peso){
        $groupLog = '----------cotizacionNacional----------'.PHP_EOL;
        Validator::make($request->all(),[
            'codigo_postal_destino' => 'required|digits:5',
        ])->validate();

        $tipoNegociacion = $comercio->id_negociacion;

        //busca los cp en tabla sepomex si no encuentra alguno lo inserta
        $sepomex = new Sepomex();
        if (!$sepomex->buscarCP($request->codigo_postal_origen)) {
            $groupLog .= 'No encontro cp origen en sepomex'.PHP_EOL;
            $this->nuevoCp($request, $request->codigo_postal_origen);
        }
        if (!$sepomex->buscarCP($request->codigo_postal_destino)) {
            $groupLog .='No encontro cp destino en sepomex'.PHP_EOL;
            $this->nuevoCp($request, $request->codigo_postal_destino);
        }

        //Tafificador Zonas Web;
        //die(print_r($comercio->id_configuracion));
        if(in_array($comercio->id_configuracion, ConfiguracionComercio::$comerciosZonas)){
            $tarificadorCollect = $this->cotizadorZonasWeb($mensajeriasCostos, $comercio,$tipoNegociacion, $request, $paquete, $peso, $seguro);

        }else{
            $tarificadorCollect = $this->cotizadorPrepago($mensajeriasCostos, $comercio,$comercioUsuario,$tipoNegociacion,$request,$paquete,
            $comercioPaquete,$peso, $seguro,$mensajeriasIds);     
        }
        Log::info($groupLog);
        return $tarificadorCollect;
    }

    private function cotizadorPrepago($mensajeriasCostos, $comercio,$comercioUsuario,$tipoNegociacion,$request,$paquete, $comercioPaquete,$peso, $seguro,$mensajeriasIds){
        $groupLog = '----------cotizadorPrepago----------'.PHP_EOL;
        $tarificadorCollect = collect();
        $comercioIdIdentity = $comercio->clave;
        $serviciosMemsajerias = ServicioMensajeria::whereIn('mensajeria_id', $mensajeriasIds)->get();
        
        if(in_array($comercio->id_configuracion, ConfiguracionComercio::$comerciosCustom)) {
            $groupLog.='Tarifas Custom TarifaMensajeriaComercio'.PHP_EOL;
            $tarifasMensajerias = TarifaMensajeriaComercio::whereIn('id_mensajeria', $mensajeriasIds)
                ->whereIn('id_servicio_mensajeria', $serviciosMemsajerias->pluck('id', 'id'))
                ->where('peso', $peso)
                ->where('id_comercio', $comercio->id)
                ->get();
            $nivelConfiguraciones = null;
        } elseif (in_array($comercio->id_configuracion, [2, 9])) {
            $groupLog.='Tarifas con tablas y paquetes TarifaMensajeria'.PHP_EOL;
            $tarifasMensajerias = TarifaMensajeria::whereIn('id_mensajeria', $mensajeriasIds)
                ->whereIn('id_servicio_mensajeria', $serviciosMemsajerias->pluck('id', 'id'))
                ->where('peso', $peso)
                ->get();

            $comercioNivel = ComercioNiveles::join('niveles','niveles.id_nivel','comercios_niveles.id_nivel')->where('comercios_niveles.id_comercio', $comercio->id)->first();

            if( $comercioNivel){

                $nivelConfiguraciones = NivelConfiguracion::where('id_nivel', $comercioNivel->id_nivel)->get();
            }else{
                $nivelConfiguraciones = null;
            }
        }

        $coberturasMensajerias = CoberturaCPMensajeria::whereIn('id_mensajeria', $mensajeriasIds)
        ->whereIn('codigo_postal', [$request->codigo_postal_origen, $request->codigo_postal_destino])
        ->get();

        $tarificadorNiveles = null;
        if ($nivelConfiguraciones) {
            $groupLog.='Tarifas con Niveles'.PHP_EOL;
            $idsMensajeriasEliminar = $nivelConfiguraciones->pluck('id_mensajeria')->toArray();
            $mensajeriasCostos = $mensajeriasCostos->reject(function ($elemento) use ($idsMensajeriasEliminar) {
                return in_array($elemento->id, $idsMensajeriasEliminar);
            });

            $tarificadorNiveles = $this->cotizadorNiveles($nivelConfiguraciones, $peso, $request, $comercio, $comercioNivel->nombre);
        }

        //tarificador por tablas/paquetes
        $tarificadorPaquetes = collect();
        $comercioPaquete = !empty($comercioPaquete) ? $comercioPaquete->id_paquete: 1;

        foreach ($mensajeriasCostos as $mensajeriaCosto) {
            
            if (!property_exists($mensajeriaCosto, 'mensajeria_id')) {
                $mensajeriaCosto->mensajeria_id = $mensajeriaCosto->id;
                $mensajeriaCosto->comercio_id = $comercio->id;
                $mensajeriaCosto->negociacion_id = $tipoNegociacion;
            }

            $mensajeria = Mensajeria::findOrFail($mensajeriaCosto->mensajeria_id);
            //$mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request, $comercioPaquete,true,$peso);
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request, $comercioPaquete,$peso,true);
            $mensajeriaTO->setIdConfiguracion($comercio->id_configuracion);
            $mensajeriaTO->setTabulador(true);

            $groupLog.='Mensajeria: ' . $mensajeria->clave.PHP_EOL;
            $groupLog.='Configuracion: ' . $comercio->id_configuracion.PHP_EOL;

            // $accesoComercioDhl = AccesoComercioDhl::pluck('id_comercio');
            // $comerciosDhl = $accesoComercioDhl->toArray();
            // die(print_r($mensajeriasCostos));

            //if ($mensajeriaCosto->mensajeria_id != 1 || in_array($comercio->id, $comerciosDhl)) {
                if (class_exists($mensajeria->clase)) {
                    $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);

                    if ($mensajeriaEmpresa instanceof MensajeriaCotizable) {

                        $groupLog.='Verificar cobertura'.PHP_EOL;
                        $coberturaOrigen = $coberturasMensajerias->where('id_mensajeria', $mensajeria->id)->where('codigo_postal', $request->codigo_postal_origen)->first();
                        $coberturaDestino = $coberturasMensajerias->where('id_mensajeria', $mensajeria->id)->where('codigo_postal', $request->codigo_postal_destino)->first();

                        $tarificador = $mensajeriaEmpresa->responseTarificadorTab($mensajeria, $seguro, $comercioUsuario->comercio_id, $comercioIdIdentity, $paquete, $mensajeriaTO->getNumeroExterno(), false);
                        $location = env('API_LOCATION');

                        if ($coberturaOrigen && $coberturaDestino) {
                            $tarificador->cotizacion = new stdClass();
                            $tarificador->cotizacion->success = true;

                            $groupLog.='Busca tarifa'.PHP_EOL;
                            $tarifas = $tarifasMensajerias->where('id_paquete', $comercioPaquete)->where('id_mensajeria', $mensajeria->id)->where('peso', $peso);
                           // $tarificador->cotizacion->message = Response::$messages['successfulSearch'];
                            //$tarificador->cotizacion->code_response = 200;
                            $tarificador->cotizacion->servicios = new stdClass();

                            foreach ($tarifas as $tarifa) {
                                $servicioMensajeria = $serviciosMemsajerias->firstWhere('id', $tarifa->id_servicio_mensajeria);
                                $groupLog.="Servicio " . $servicioMensajeria->nombre.PHP_EOL;

                                $costo = $tarifa->precio;
                                $costoAdicional = 0;
                                $costoSeguro = 0;
                                $zonaExtendida = 0;
                                $groupLog.=' Costo guia ' . $mensajeria->clave . ':' . $costo.PHP_EOL;
                                if ($request->seguro) {

                                    if ($mensajeria->clave == 'DHL' || $mensajeria->clave == 'FEDEX' || $mensajeria->clave == 'REDPACK') {
                                        $costoSeguroCalculado = $request->valor_paquete * ($mensajeriaCosto->porcentaje_seguro / 100);
                                        $costoSeguro = $mensajeriaCosto->costo_seguro + $costoSeguroCalculado;
                                        $groupLog.=' Costo base: ' . $mensajeriaCosto->costo_seguro.PHP_EOL;
                                        $groupLog.=' Porcentaje de Serguro: ' . $mensajeriaCosto->porcentaje_seguro.PHP_EOL;
                                        $groupLog.=' Serguro porcentaje calculado. ' . $costoSeguroCalculado.PHP_EOL;
                                        $groupLog.=' Costo total seguro: ' . $costoSeguro.PHP_EOL;
                                        //                                    $costoTotalCalaculado = round($costo + $costoSeguro, 2);
                                    } else {

                                        if ($mensajeriaCosto->porcentaje_seguro) {
                                            $costoSeguro = round($request->valor_paquete * ($mensajeriaCosto->porcentaje_seguro / 100), 2);
                                        } else {
                                            $costoSeguro = $mensajeriaCosto->costo_seguro;
                                        }
                                    }
                                }

                                //Verifica si los codigos postales son zona extendida
                                if ($mensajeria->clave == 'REDPACK' && $coberturaDestino->zona_extendida) {
                                    $costosMensajerias = new CostosSegurosRedpack();
                                    $zonaExtendida = $costosMensajerias->getCostoZonaExtendida($peso, $servicioMensajeria->nombre);
                                } elseif ($coberturaOrigen->zona_extendida == 1 || $coberturaDestino->zona_extendida == 1) {
                                    $zonaExtendida = $mensajeriaCosto->costo_zona_extendida;
                                }

                                $groupLog.=' Costo zona extendida ' . $zonaExtendida.PHP_EOL;
                                $costoTotalCalaculado=0;
                                //valida si es de multiguias y fedex y dhl
                                if(isset($request->paquetes) && $request->paquetes > 1){
                                    if($mensajeria->clave == 'DHL' || $mensajeria->clave == 'FEDEX'){
                                        $totalPaq = $request->paquetes;
                                        //Multiplica el costo por la cantidad de paquetes
                                        $costoTotalCalaculado = ( round($costo + $costoAdicional + $costoSeguro + $zonaExtendida, 2) ) * $totalPaq;
                                    }
    
                                }else{
                                    $costoTotalCalaculado = round($costo + $costoAdicional + $costoSeguro + $zonaExtendida, 2);
                                }
                                $groupLog.=' Costo Total: ' . $costoTotalCalaculado.PHP_EOL;

                                if ($mensajeria->clave == 'IVOY') {
                                    $serviciosIvoy = $serviciosMemsajerias->where('mensajeria_id', $mensajeria->id);

                                    foreach ($serviciosIvoy as $servicioIvoy) {
                                        $fechaEntrega = $mensajeriaEmpresa->obtenerFechaEntrega($servicioIvoy->nombre);
                                        $servicio = $mensajeriaEmpresa->responseServiceTab($costo, $costo, $costoTotalCalaculado, $servicioIvoy, $zonaExtendida, $fechaEntrega, $costoSeguro);
                                        $tarificador->cotizacion->servicios->{$servicioIvoy->nombre} = $servicio;
                                    }
                                } else {

                                    $fechaEntrega = $mensajeriaEmpresa->obtenerFechaEntrega($servicioMensajeria->nombre);
                                    $servicio = $mensajeriaEmpresa->responseServiceTab($costo, $costo, $costoTotalCalaculado, $servicioMensajeria, $zonaExtendida, $fechaEntrega, $costoSeguro);
                                    $tarificador->cotizacion->servicios->{$servicioMensajeria->nombre} = $servicio;
                                }
                            }
                            $tarificador->cotizacion->location = $location;
                            //valida si es de multiguias y fedex y dhl
                            if(isset($request->paquetes) && $request->paquetes > 1){
                                if($mensajeria->clave == 'DHL' || $mensajeria->clave == 'FEDEX'){ //si no es fedex o dhl no se muestra
                                    $tarificadorPaquetes->push($tarificador);
                                }

                            }else{
                                $tarificadorPaquetes->push($tarificador);
                            }


                        } else {
                            $tarificador->cotizacion = new stdClass();
                            $tarificador->cotizacion->success = false;
                            $tarificador->cotizacion->message = Response::$messages['noCoverage'];
                            $tarificador->cotizacion->code_response = 400;
                            $tarificador->cotizacion->servicios = new stdClass();
                            $tarificadorPaquetes->push($tarificador);
                        }
                    } else {
                        $groupLog.="No es cotizable".PHP_EOL;
                    }
                } else {
                    $groupLog.="No existe la clase".PHP_EOL;
                }
            //}
            Log::info($groupLog."...............................");
        }
        if($tarificadorNiveles){
            $tarificadorCollect = $tarificadorNiveles->merge($tarificadorPaquetes);
        }else{
            $tarificadorCollect = $tarificadorPaquetes;
        }
        //die(print_r($tarificadorCollect->toArray()));
        $this->mensajeriaService->guardarTarificadorCotizaciones($tarificadorCollect, $mensajeriaTO, $request->productos);
        return $tarificadorCollect;
    }

    private function cotizadorZonasWeb($mensajeriasCostos, $comercio, $tipoNegociacion, $request, $paquete, $peso, $seguro){
        $tarificadorCollect = collect();
        $groupLog='----------cotizadorZonasWeb----------'.PHP_EOL;
        foreach ($mensajeriasCostos as $mensajeriaCosto) {
            //die(var_dump(isset($mensajeriaCosto)));
            if (!isset($mensajeriaCosto->mensajeria_id)) {
                $mensajeriaCosto->mensajeria_id = $mensajeriaCosto->id;
                $mensajeriaCosto->comercio_id = $comercio->id;
                $mensajeriaCosto->negociacion_id = $tipoNegociacion;
            }

            $mensajeria = Mensajeria::findOrFail($mensajeriaCosto->mensajeria_id);
            $groupLog.='......Mensajeria: ' . $mensajeria->clave.'......'.PHP_EOL;
            $groupLog.='Configuracion: ' . $comercio->id_configuracion.PHP_EOL;

            //$mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request, $paquete,false, $peso);
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request, $paquete,$peso,false);
            $mensajeriaTO->setIdConfiguracion($comercio->id_configuracion);
            //die(print_r($mensajeriaTO));

            if (class_exists($mensajeria->clase)) {
                $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);

                if ($mensajeriaEmpresa instanceof MensajeriaCotizable) {
                    $tarificador = $mensajeriaEmpresa->responseTarificador($mensajeria, $seguro, $comercio->id);
                    $tarificador->cotizacion = $mensajeriaEmpresa->rate(true);

                    if (isset($tarificador->cotizacion->success)){
                        //valida si es de multiguias y fedex y dhl
                        if(isset($request->paquetes) && $request->paquetes > 1){
                            if($mensajeria->clave == 'DHL' || $mensajeria->clave == 'FEDEX'){ //si no es fedex o dhl no se muestra
                                $tarificadorCollect->push($tarificador);
                            }

                        }else{
                            $tarificadorCollect->push($tarificador);
                        }
                    }
                } else {
                    $groupLog.="No es cotizable".PHP_EOL;
                }
            } else {
                $groupLog.="No existe la clase".PHP_EOL;
            }
        }

        $this->mensajeriaService->guardarTarificadorCotizaciones($tarificadorCollect, $mensajeriaTO, $request->productos);
        Log::info($groupLog);
        return $tarificadorCollect;

    }

    private function cotizadorNiveles($nivelConfiguraciones,$peso,$request,$comercio,$nivel){
        $tarificadorCollect = collect();
        $groupLog='Cotizador Niveles'.PHP_EOL;
        foreach ($nivelConfiguraciones as $mensajeriaMargen) {

            $mensajeria = Mensajeria::findOrFail($mensajeriaMargen->id_mensajeria);
            $groupLog.'......Mensajeria: ' . $mensajeria->clave.'......'.PHP_EOL;
           // Log::info('Nivel: ' . $comercio->id_configuracion);
            $mensajeriaMargen->porcentaje = $mensajeriaMargen->margen;
            //$mensajeriaTO = $this->setDatosMensajeria($mensajeriaMargen, $request, null,false, $peso);,
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaMargen, $request, null,$peso,false);
            $mensajeriaTO->setComercio($comercio->id);
            $mensajeriaTO->setId( $mensajeria->id);
            $mensajeriaTO->setIdConfiguracion($comercio->id_configuracion);
            $mensajeriaTO->setNegociacionId($comercio->id_negociacion);
            $mensajeriaTO->setPorcentajeSeguro(0);
            $mensajeriaTO->setCosto(0);

            if (class_exists($mensajeria->clase)) {
                $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);

                $tarificador = $mensajeriaEmpresa->responseTarificadorTab($mensajeria, 0, $comercio->id,$comercio->clave,null,null,false,$comercio->id_configuracion,$nivel);
                $tarificador->cotizacion = $mensajeriaEmpresa->rate(true);

                if (isset($tarificador->cotizacion->success)){
                    if(isset($request->paquetes) && $request->paquetes > 1){
                        if($mensajeria->clave == 'DHL' || $mensajeria->clave == 'FEDEX'){ //si no es fedex o dhl no se muestra
                            $tarificadorCollect->push($tarificador);
                        }

                    }else{
                        $tarificadorCollect->push($tarificador);
                    }
                }

            } else {
                Log::Error("No existe la clase:". $mensajeria->clase);
            }
        }

        $this->mensajeriaService->guardarTarificadorCotizaciones($tarificadorCollect, $mensajeriaTO, $request->productos);
       // die(print_r($tarificadorCollect));
        Log::info($groupLog);
        return $tarificadorCollect;
    }

    private function cotizacionInternacional($request,$seguro,$mensajeriasCostos,$comercio,$comercioPaquete,$comercioUsuario,$peso){
        $comercioIdIdentity = $comercio->clave;
        $tipoNegociacion = $comercio->id_negociacion;
        $groupLog = '----------cotizacionInternacional-----------'.PHP_EOL;
        if(!in_array($comercio->id_configuracion,ConfiguracionComercio::$comerciosZonas)){
            $paquete = $comercioPaquete->nombre;
            $idPaquete = $comercioPaquete->id_paquete;
        }else{
            $paquete = null;
            $idPaquete = null;
        }

        $tarificadorCollect = collect();

        foreach ($mensajeriasCostos as $mensajeriaCosto) {
            if(!$mensajeriaCosto->mensajeria_id){
                $mensajeriaCosto->mensajeria_id = $mensajeriaCosto->id;
                $mensajeriaCosto->comercio_id = $comercio->id;
                $mensajeriaCosto->negociacion_id = $tipoNegociacion;
            }

            $mensajeria = Mensajeria::findOrFail($mensajeriaCosto->mensajeria_id);
            //$mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request,$idPaquete, false,$peso);
            $mensajeriaTO = $this->setDatosMensajeria($mensajeriaCosto, $request,$idPaquete,$peso,false);
            $mensajeriaTO->setIdConfiguracion($comercio->id_configuracion);

            $groupLog .='Mensajeria: ' . $mensajeria->clave.PHP_EOL;
            $groupLog .='Negociacion: ' . $mensajeriaCosto->negociacion_id.PHP_EOL;

            if (class_exists($mensajeria->clase)){
                $mensajeriaEmpresa = new $mensajeria->clase($mensajeriaTO);

                if ($mensajeriaEmpresa instanceof MensajeriaCotizable) {
                    $tarificador = $mensajeriaEmpresa->responseTarificadorTab($mensajeria, $seguro, $comercioUsuario->comercio_id, $comercioIdIdentity,
                    $paquete,$mensajeriaTO->getNumeroExterno(),$request->envio_internacional,$comercio->id_configuracion);
                    $location = env('API_LOCATION');

                    if($mensajeria->clave == 'FEDEX'){
                        $tarificador->cotizacion = $mensajeriaEmpresa->rateInternational(true,$seguro);

                        if (isset($tarificador->cotizacion->success)){
                            $tarificadorCollect->push($tarificador);
                        }
                    }

                } else {
                    $groupLog.="No es cotizable".PHP_EOL;
                }
            }
            else {
                $groupLog.="No existe la clase".PHP_EOL;
            }
            Log::info($groupLog."...............................");
        }
        $this->mensajeriaService->guardarTarificadorCotizaciones($tarificadorCollect, $mensajeriaTO);

        return $tarificadorCollect;
    }

    /**
     * @param $comercioId
     * @param $mensajeriasIds
     * @return mixed
     * @throws \Exception
     */
    private function obtenerCostos($comercioId, $mensajeriasIds){
        $mensajeriasCostos = CostoMensajeria::where('comercio_id',$comercioId)->whereIn('mensajeria_id',$mensajeriasIds)
            ->where('is_deleted',0)->get();

        if($mensajeriasCostos->count() == 0){
            throw new  \Exception( 'No cuenta con negociacion configurada');
        }

        return $mensajeriasCostos;
    }

    private function buscarComercioUsuario($comercioId){
        $comercioUsuario = User::where('email', Auth::user()->email)
            ->join('usuarios_comercios','usuarios_comercios.usuario_id','usuarios.id')
            ->where('usuarios_comercios.comercio_id',$comercioId)
            ->first();

        if(!$comercioUsuario){
            throw new  \Exception( 'No se encotro comercio o usuario');
        }

        return $comercioUsuario;
    }

    //private function setDatosMensajeria($mensajeriaCosto, $request,$paquete,$tabulador = false,$pesoCalculado){
    private function setDatosMensajeria($mensajeriaCosto, $request,$paquete,$pesoCalculado,$tabulador = false){
        $mensajeriaTO = new MensajeriaTO($request->all());
        $mensajeriaTO->buscarSiglasSepomex();
        $mensajeriaTO->setComercio($mensajeriaCosto->comercio_id);
        $mensajeriaTO->setId($mensajeriaCosto->mensajeria_id);
        $mensajeriaTO->setPorcentaje($mensajeriaCosto->porcentaje);
        $mensajeriaTO->setCosto($mensajeriaCosto->costo);
        $mensajeriaTO->setCostoAdicional($mensajeriaCosto->costo_adicional);
        $mensajeriaTO->setNegociacionId($mensajeriaCosto->negociacion_id);
        $mensajeriaTO->setPorcentajeSeguro($mensajeriaCosto->porcentaje_seguro);
        $mensajeriaTO->setCostoSeguro($mensajeriaCosto->costo_seguro);
        $mensajeriaTO->setCostoZonaExtendida($mensajeriaCosto->costo_zona_extendida);
        $mensajeriaTO->setPaqueteComercio($paquete);
        $mensajeriaTO->setTabulador($tabulador);
        $mensajeriaTO->setNumeroExterno($request->pedido_comercio);
        $mensajeriaTO->setPaisDestino($request->pais_destino);
        $mensajeriaTO->setPesoCalculado($pesoCalculado);

        return $mensajeriaTO;
    }
    private function validaciones($request,$comercioUsuario,$mensajeriasCostos){

        Log::info('Verifica que comercio pertenezca a usuario');
        if(!$comercioUsuario){
            throw new \Exception("Comercio {$request->comercio_id} no diponible para el usuario");
        }

        if(!$mensajeriasCostos){
            throw new \Exception("Comercio {$request->comercio_id} no cuenta con configuracion de costos");
        }

    }
    private function nuevoCP($request, $nuevoCp){
        $sepomexTO = new SepomexTO();
        $sepomexTO->setDCodigo($nuevoCp);
        $sepomex = new Sepomex();
        $sepomex->nuevo($sepomexTO);
    }
}
