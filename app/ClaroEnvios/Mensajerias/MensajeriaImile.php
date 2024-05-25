<?php
namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Comercios\CamposLimitesMensajerias\CampoLimiteMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoCampoMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeria;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaResponse;
use App\ClaroEnvios\Mensajerias\Guias\GuiaMensajeriaResponse;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Uber\TiendaUber;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\ZPL\ZPL;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use SKAgarwal\GoogleApi\PlacesApi;
use \Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaImile
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 * @author Roberto Martinez
 */
class MensajeriaImile extends MensajeriaMaster implements MensajeriaCotizable
{

    private $service;

    protected $porcentaje;
    protected $peso;
    protected $largo;
    protected $ancho;
    protected $alto;
    protected $dias_embarque;
    protected $codigo_postal_origen;
    protected $codigo_postal_destino;
    private $siglas_codigo_postal_destino;
    private $siglas_codigo_postal_origen;
    protected $guia_mensajeria;
    protected $costo;
    protected $porcentaje_seguro;
    protected $valor_paquete;
    protected $formato_guia_impresion;
    protected $extension_guia_impresion;
    protected $fecha_liberacion;
    protected $request;
    protected $response;
    protected $location;
    protected $negociacion_id;
    protected $tipo_paquete;
    protected $costo_adicional;
    private $endpointLabel;
    private $endpointLogin;
    protected $porcentaje_calculado;
    protected $costo_calculado;
    protected $seguro;
    private $equipo;
    private $pedido;
    private $tipo;
    private $ID;
    private $url_tracking;

    private $keys;
    private $tiendaId;
    private $code_response;

    private $comercioNegociacionID;
    protected $id_configuracion;

    private $arrayLabelUrl = [
        'PRODUCCION'=>"https://openapi.imile.com/",
        'TEST'=>"https://openapi.52imile.cn/"

    ];

    private $arrayLoginUrl = [
        'PRODUCCION'=>"https://openapi.imile.com/auth/accessToken/grant",
        'TEST'=>"https://openapi.52imile.cn/auth/accessToken/grant"

    ];

    use AccesoConfiguracionMensajeria;


    public function __construct($mensajeriaTO = false)
    {

        if ($mensajeriaTO instanceof MensajeriaTO) {
            $this->location = env('API_LOCATION', 'test');

            $this->costo = $mensajeriaTO->getCosto();
            $this->porcentaje = $mensajeriaTO->getPorcentaje();
            $this->porcentaje_seguro = $mensajeriaTO->getPorcentajeSeguro();
            $this->negociacion_id = $mensajeriaTO->getNegociacionId();
            $this->peso = $mensajeriaTO->getPeso();
            $this->largo = $mensajeriaTO->getLargo();
            $this->ancho = $mensajeriaTO->getAncho();
            $this->alto = $mensajeriaTO->getAlto();
            $this->dias_embarque = $mensajeriaTO->getDiasEmbarque();
            $this->codigo_postal_origen = $mensajeriaTO->getCodigoPostalOrigen();
            $this->codigo_postal_destino = $mensajeriaTO->getCodigoPostalDestino();
            $this->siglas_codigo_postal_destino = $mensajeriaTO->getSiglasCodigoDestino();
            $this->siglas_codigo_postal_origen = $mensajeriaTO->getSiglasCodigoOrigen();
            $this->valor_paquete = $mensajeriaTO->getValorPaquete();
            $this->formato_guia_impresion = $mensajeriaTO->getFormatoGuiaImpresion();
            $this->extension_guia_impresion = $mensajeriaTO->getExtensionGuiaImpresion();
            $this->fecha_liberacion = $mensajeriaTO->getFechaLiberacion();
            $this->tipo_paquete = $mensajeriaTO->getTipoPaquete();
            $this->costo_adicional = $mensajeriaTO->getCostoAdicional();
            $this->seguro = $mensajeriaTO->getSeguro();
            $this->equipo = $mensajeriaTO->getEquipo();
            $this->pedido = $mensajeriaTO->getPedido();
            $this->tipo = $mensajeriaTO->getTipo();
            $this->tiendaId = $mensajeriaTO->getTiendaId();
            $this->ID = $mensajeriaTO->getId();
            $this->comercioNegociacionID = Auth::user()->comercio_id;
            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();

            if($mensajeriaTO->getNegociacionId() == 1 ){
                $accesoComercioMensajeriaTO->setComercioId(1);
                $this->comercioNegociacionID = 1;
            }

            Log::info('Comercio: '.$mensajeriaTO->getComercio().', '.'Negociacion: '.$mensajeriaTO->getNegociacionId());
            Log::info('Llaves comercio: '.$this->comercioNegociacionID);
            $this->configurarAccesos($accesoComercioMensajeriaTO);

            if(!$this->configuracion){
                $this->configuracion = collect();
            }
            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                $this->endpointLogin = $this->arrayLoginUrl['PRODUCCION'];
                $this->url_tracking = "https://tracking.cargamos.com/order-detail/";
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->endpointLogin = $this->arrayLoginUrl['TEST'];
                $this->url_tracking = "https://tracking.cargamos.com/order-detail/";
            }


        }
    }

    /**
     * Otorga una guia
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO){
        try {
            $destino = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
            $origen = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
            $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();

            if($this->configuracion->count() < 3){
                throw new \Exception("No cuenta con credenciales de mensajeria");
            }


            $array = [];
            // $placeApiDestino = $this->searchPlaceID($destino);

            //Busca si hay tiendas disponibles
            // $this->findStore($placeApiDestino);
            // $estimate = $this->createEstimate($tiendaOrigen, $placeApiDestino, $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId(),$this->pedido);
            $delivery = $this->createDelivery($destino, $guiaMensajeriaTO,$origen);

            //Intenta crear ZPL

            $file = base64_decode($delivery->data->imileAwb);

            $tracking_id = $delivery->data->expressNo;
            $tmpPath = sys_get_temp_dir();
            $rutaArchivo = $tmpPath . ('/' . $tracking_id  . '_' . date('YmdHis') . '.' . $this->extension_guia_impresion);
            file_put_contents($rutaArchivo, $file);

            $nombreArchivo = $tracking_id . '_' . date('YmdHis') . '.pdf';
            $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file) : base64_encode($file);

            $array['guia'] = $tracking_id;
            $array['imagen'] = $dataFile;
            $array['extension'] = "pdf";
            $array['nombreArchivo'] = $nombreArchivo;
            $array['ruta'] = $rutaArchivo;
            $array['link_rastreo_entrega'] = env('TRACKING_LINK_T1ENVIOS')."".$tracking_id;
            $array['location'] = (env('API_LOCATION') == 'test') ? $this->endpointLabel : env('API_LOCATION');
            $array['infoExtra']=[
                'codigo'=>'301',
                'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
                'identificadorUnico'=>'',
                'tracking_link' => env('TRACKING_LINK_T1ENVIOS')."".$tracking_id
            ];

        }catch(\Exception $exception){
            Log::error($exception->getFile().' '.$exception->getLine());
            throw new \Exception($exception->getMessage());
        }

        return $array;
    }

    private function findStore(Collection $placeApiDestino){
//         Log::info('Entra a findStore');
//         $placeId = $placeApiDestino->get('place_id');
//         $url = "stores?place_id=".$placeId."&place_provider=google_places";
// //        $url = "stores?place_id=ChIJgcRS0EHw0YURr8eHDRZUn_0&place_provider=google_places";

//         $response =  $this->makeRequest([],$url,"GET");
//         Log::info(json_encode($response));

//         if(property_exists($response,'stores')){
//             if(count($response->stores)== 0){
//                 $message = Response::$messages['noCoverage'];
//                 throw new \Exception($message);
//             }
//         }

//         return $response;

    }

    private function createEstimate(TiendaUber $tiendaUberOrigen,Collection $placeApiDestino,$bitacoraCotizacionId){

        // try{
        //     Log::info('Entra en createEstimate ');
        //     Log::info("Client_id: ".$this->configuracion->get('client_id'));

        //     $placeId = $placeApiDestino->get('place_id');
        //     $now = Carbon::now();
        //     $segundos = $now->addMinutes(15)->getTimestamp();
        //     $pickupTimes = $segundos * 1000;
        //     //        $date2 = date("d M Y H:i",$pickupTimes);
        //     $data = [
        //         "pickup"=> [
        //             "store_id"=> "$tiendaUberOrigen->uuid_uber"
        //         ],
        //         "dropoff_address"=> [
        //             "place"=>[
        //                 "id"=> $placeId,
        //                 "provider"=> "google_places"
        //             ]
        //         ],
        //         "pickup_times"=> [$pickupTimes]
        //     ];

        //     $cotizacion = BitacoraCotizacionMensajeria::find($bitacoraCotizacionId);
        //     $response =  $this->makeRequest($data,'estimates','POST',$cotizacion->id);
        //     $this->guardaCotizacionResponse($bitacoraCotizacionId);
        //     $costoGuia = ($response->estimates[0]->delivery_fee->total)/100;

        //     $costoAdicional = 0;

        //     if ($cotizacion->costo_convenio != 0) {
        //         $costoAdicional = $cotizacion->costo_convenio;
        //     }elseif ($cotizacion->porcentaje != 0){
        //         $costoAdicional = round($costoGuia*($cotizacion->porcentaje/100), 2);
        //     }
        //     $costoSeguro = $cotizacion->seguro?round($cotizacion->valor_paquete*($cotizacion->porcentaje_seguro/100), 4):0;

        //     Log::info(' Costo Serguro '.$costoSeguro);
        //     Log::info(' Costo Adicional '.$costoAdicional);

        //     $costoTotalCalaculado = round($costoGuia + $costoAdicional + $costoSeguro, 4);
        //     $cotizacion->costo = $costoGuia;
        //     $cotizacion->costo_porcentaje = $costoTotalCalaculado;
        //     $cotizacion->updated_at = Carbon::now();
        //     $cotizacion->updated_usuario_id = auth()->user()->id;
        //     $cotizacion->token = $response->estimate_id;
        //     $cotizacion->save();
        //     Log::info("Estimate Id: ".$response->estimate_id);

        //     return $response;

        // }catch (\Exception $exception){
        //     $error = $this->parseError( $exception->getMessage());
        //     $token = $this->configuracion->get('token');
        //     $options = [
        //         "json" => $data,
        //         'connect_timeout' => 90,
        //         'http_errors' => true,
        //         'verify' => false,
        //         'headers' => [
        //             'Content-Type' => 'application/json',
        //             'Authorization' => 'Bearer ' . $token
        //         ]
        //     ];
        //         $this->setResponse($error->get('json'));
        //         $this->setRequest(json_encode($options));
        //         $this->setCodeResponse($exception->getCode());
        //         $this->guardaCotizacionResponse($bitacoraCotizacionId);

        //     throw new \Exception($error->get('message'),$exception->getCode());
        // }

    }



    /**
     * @param $data
     * @param string $type estimate|orders
     * @param string $method
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleExmception
     */
    private function makeRequest($data,$type, $method = 'POST',$bitacoraCotizacionId = null){

        $token = $this->configuracion->get('token');

        Log::info('TOKEN makeRequest: '.$token);

        $timestamp = (int) round(Carbon::now()->format('Uu') / pow(10, 6 - 3));
        $req = json_encode($data);
        $cadena = $this->configuracion->get('apikey').'accessToken'.$token.'customerId'.$this->configuracion->get('customerId').'formatjsonsignMethodMD5timeZone+5timestamp'.$timestamp.'version1.0.0'.$req.$this->configuracion->get('apikey');

        $cifrado = strtoupper( md5($cadena) );

        Log::info("timestamp: ". $timestamp);
        Log::info("cadena: ". $cadena);
        Log::info("cifrado: ". $cifrado);

        $options = [
            "json" => [
                "customerId" => $this->configuracion->get('customerId'),
                "sign" => $cifrado,
                "accessToken"=> $token,
                "timestamp" => $timestamp,
                "signMethod" => "MD5",
                "format" => "json",
                "version" => "1.0.0",
                "timeZone"=> "+5",
                "param" =>  $data
            ],
            'connect_timeout' => 90,
            'http_errors' => true,
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        try{

            if ($token) {
                Log::info('Trae token: '.$token);
                $client = new Client();
                $response = $client->request($method, $this->endpointLabel . $type, $options);

                Log::info('status MakeRequest ');
                $statusResponse = $response->getStatusCode();
                Log::info($statusResponse );
                Log::info('Content MakeRequest ');
                $content = $response->getBody()->getContents();
                Log::info($content );
                $this->setResponse($content);
                $this->setRequest(json_encode($options));
                $this->setCodeResponse($statusResponse);

                $codeResponse = json_decode($content);

                if ($codeResponse->code == "407") {
                    $response=$this->reintentarToken($data, $type, $method, $bitacoraCotizacionId);
                    return $response;
                }

                if (isset($codeResponse->data)) {
                    $vacio = $codeResponse->data;

                    if (empty((array)$vacio) || is_null($vacio)) {
                        Log::error("Data vacio, existe un error de mensajeria: ".serialize($codeResponse));
                        throw new \Exception("Exite un error de mensajeria: ".$codeResponse->message, 400);
                    }
                }
                else{
                    Log::error("NO CONTIENE data: ". serialize($codeResponse));
                    throw new \Exception("Exite un error de mensajeria: ".$codeResponse->message,400);
                }

                return json_decode($content);

            } else {

                Log::info("Primer Token :");

                $response=$this->reintentarToken($data, $type, $method, $bitacoraCotizacionId);
                return $response;

            }

        }catch (\GuzzleHttp\Exception\ClientException $exception){
            $error = $exception->getResponse()->getBody()->getContents();
            Log::error("Error makeRequest: ".$exception->getMessage().': '.$exception->getFile().' '.$exception->getLine());
            Log::error($exception->getResponse()->getBody()->getContents());
//            die(print_r($exception->getMessage().': '.$exception->getFile().' '.$exception->getLine()));
            if($exception->getCode() == 401){
                Log::error("Regresa 401:".$this->endpointLabel.$type);

                $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->ID)
                    ->where('clave','token')
                    ->first();

                $accesoComercio = AccesoComercioMensajeria::where('mensajeria_id',$this->ID)
                    ->where('comercio_id',$this->comercioNegociacionID)
                    ->where('acceso_campo_mensajeria_id',$accesoCampo->id)
                    ->first();

                if($accesoComercio){
                    $tokenActual = $this->configuracion->get('token');
                    Log::info("Token actual: ".$tokenActual);

                    $tokenCargamos = $this->getToken();

                    $this->actualizaToken($tokenCargamos);
                    $response= $this->requestImile($data,$type,$method,$bitacoraCotizacionId);

                }
                else{
                    Log::info("Nuevo Token:");
                    $token = $this->getToken();
                    $this->guardaToken($token,$accesoCampo);

                    $this->configuracion->put('token',$token);
                    $response= $this->requestImile($data,$type,$method,$bitacoraCotizacionId);
                }

                return $response;

            }else{
                $error = json_decode($error);
                $e = json_encode($error->errors["0"]);
                Log::error($e);
                throw new \Exception($e, $exception->getCode());
            }
        }
    }


    private function reintentarToken($data, $type, $method, $bitacoraCotizacionId){
        $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->ID)
        ->where('clave', 'token')
        ->first();

        $accesoComercio = AccesoComercioMensajeria::where('mensajeria_id',$this->ID)
            ->where('comercio_id',$this->comercioNegociacionID)
            ->where('acceso_campo_mensajeria_id',$accesoCampo->id)
            ->first();

        $token = $this->getToken();

        if($accesoComercio){
            $this->actualizaToken($token);
        }else{
            $this->guardaToken($token, $accesoCampo);
        }

        $this->configuracion->put('token', $token);
        $response = $this->requestImile($data, $type, $method,$bitacoraCotizacionId);

        return $response;
    }

    private function guardaCotizacionResponse($bitacoraCotizacionId){
        $cotizacion = BitacoraCotizacionMensajeria::find($bitacoraCotizacionId);
        $bitacoraCotizacionMensajeriaResponse = new BitacoraCotizacionMensajeriaResponse();
        $bitacoraCotizacionMensajeriaResponse->bitacora_cotizacion_mensajeria_id = $cotizacion->id;
        $bitacoraCotizacionMensajeriaResponse->request =$this->request;
        $bitacoraCotizacionMensajeriaResponse->response = $this->response;
        $bitacoraCotizacionMensajeriaResponse->numero_externo = $this->pedido;
        $bitacoraCotizacionMensajeriaResponse->codigo_respuesta = $this->code_response;
        $bitacoraCotizacionMensajeriaResponse->usuario_id = auth()->user()->id;
        $bitacoraCotizacionMensajeriaResponse->save();
        Log::info("Guarda bitacora cotizacion response: ".$bitacoraCotizacionMensajeriaResponse->id.', bitacora cotizacion: '. $cotizacion->id);
    }

    private function guardaGuiaResponse($bitacoraCotizacionId){
        $guiaResponse = new GuiaMensajeriaResponse();
        $guiaResponse->request =$this->request;
        $guiaResponse->response = $this->response;
        $guiaResponse->codigo_respuesta = $this->code_response;
        $guiaResponse->usuario_id = auth()->user()->id;
        $guiaResponse->save();
        Log::info("Guarda guia mensajeria response: ".$guiaResponse->id);
    }

    private function guardaToken($token,$accesoCampo){

        Log::info("Guarda Token comercio negociacion: ".$this->comercioNegociacionID);
        $date = Carbon::now();
        $acceso = new AccesoComercioMensajeria();
        $acceso->acceso_campo_mensajeria_id = $accesoCampo->id;
        $acceso->mensajeria_id = $this->ID;
        $acceso->comercio_id = $this->comercioNegociacionID;
        $acceso->valor = $token;
        $acceso->created_at = $date->format('Y-m-d H:i:s');
        $acceso->save();

    }

    private function actualizaToken($token){

        $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->ID)
            ->where('clave','token')
            ->first();
        Log::info("Actualiza Token campo id: ".$accesoCampo->id);
        Log::info("Comercio negociacion id: ".$this->comercioNegociacionID);
        $date = Carbon::now();
        $acceso = AccesoComercioMensajeria::where('acceso_campo_mensajeria_id',$accesoCampo->id)->where('mensajeria_id',$this->ID)
            ->where('comercio_id',$this->comercioNegociacionID )
            ->firstOrFail();
        $acceso->valor = $token;
        $acceso->updated_at = $date->format('Y-m-d H:i:s');
        $acceso->update();

        $token = $acceso->valor;
        $this->configuracion->put('token',$token);

        return $token;

    }

    // consulta precios de envios
    public function rate($traerResponse = false){}

    private function getToken(){
        try{
            Log::info("Genera nuevo token");
            Log::info("Endpoint login: ". $this->endpointLogin);
            $timestamp = (int) round(Carbon::now()->format('Uu') / pow(10, 6 - 3));
            $cadena = $this->configuracion->get('apikey').'customerId'.$this->configuracion->get('customerId').'formatjsonsignMethodMD5timeZone+5timestamp'.$timestamp.'version1.0.0{"grantType":"clientCredential"}'.$this->configuracion->get('apikey');
            $cifrado = strtoupper( md5($cadena) );

            Log::info("timestamp: ". $timestamp);
            Log::info("cadena: ". $cadena);
            Log::info("cifrado: ". $cifrado);

            $options=[
                "json"=> [
                    "customerId" => $this->configuracion->get('customerId'),
                    "sign" => $cifrado,
                    "timestamp" => $timestamp,
                    "signMethod" => "MD5",
                    "format" => "json",
                    "version" => "1.0.0",
                    "timeZone"=> "+5",
                    "param" => [
                        "grantType" => "clientCredential"
                    ]
                ],
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers'  => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ];

            $client = new Client();

            $response = $client->request("POST", $this->endpointLogin,$options)->getBody()->getContents();
            $result = json_decode($response);

            Log::info("RESPONSE TOKEN:");
            Log::info($response);
            if ($result->code != "200") {
                throw new \Exception($result->message,$result->code);
            }
            return $result->data->accessToken;
        }catch (\Exception $exception){
            Log::info("ERROR getToken:");
            Log::info($exception->getMessage());
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }

    }

    /**
     * Segunda petcion cuando primera es 401
     * @param $data
     * @param $type
     * @param $method
     * @param $token
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function requestImile($data,$type,$method, $bitacoraCotizacionId = null){

        $token = $this->configuracion->get('token');
        Log::info("Peticion con nuevo token " . $this->endpointLabel . $type . ": " . $this->configuracion->get('token'));

        $timestamp = (int) round(Carbon::now()->format('Uu') / pow(10, 6 - 3));
        $req = json_encode($data);
        $cadena = $this->configuracion->get('apikey').'accessToken'.$token.'customerId'.$this->configuracion->get('customerId').'formatjsonsignMethodMD5timeZone+5timestamp'.$timestamp.'version1.0.0'.$req.$this->configuracion->get('apikey');

        $cifrado = strtoupper( md5($cadena) );

        Log::info("timestamp: ". $timestamp);
        Log::info("cadena: ". $cadena);
        Log::info("cifrado: ". $cifrado);


        $options = [
            "json" => [
                "customerId" => $this->configuracion->get('customerId'),
                "sign" => $cifrado,
                "accessToken"=> $token,
                "timestamp" => $timestamp,
                "signMethod" => "MD5",
                "format" => "json",
                "version" => "1.0.0",
                "timeZone"=> "+5",
                "param" =>  $data
            ],
            'connect_timeout' => 90,
            'http_errors' => true,
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json'
            ]
        ];

        try {

            $client = new Client();
            $response = $client->request($method, $this->endpointLabel . $type, $options);
            $statusResponse = $response->getStatusCode();
            $content = $response->getBody()->getContents();

            //Datos necesario para guardar log
            $this->setResponse($content);
            $this->setRequest(json_encode($options));
            $this->setCodeResponse($statusResponse);
            $responseLog = json_decode($content);
            Log::info("RESPONSE con nuevo token:");
            Log::info($content);
           if ($responseLog->code != "200") {
                throw new \Exception($responseLog->message,$responseLog->code);
            }
            return $responseLog;

        } catch (\Exception $exception) {
            $responseError = $exception->getMessage();
            Log::info("ERROR nueva peticion: " . $responseError);
            throw new \Exception($responseError, $exception->getCode());
        }
    }

    private function parseError($responseError){
       $json = $responseError;

        $responseCargamos = strpos($responseError,'response');

        if($responseCargamos){
            $json = substr($responseError,$responseCargamos +10);
            $messageObject= json_decode($json);
            if ($messageObject) {
                if(property_exists($messageObject,'message')){
                    $responseError = $messageObject->message;
                }
            }else {
                $responseError = $json;
            }
        }

        return collect(['json'=>$json,'message'=>$responseError]);
    }


    /**
     * @return mixed
     */
    public function getRequest()
    {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request): void
    {
        $this->request = $request;
    }


    /**
     * @param mixed $guiaMensajeria
     */
    public function setGuiaMensajeria(GuiaMensajeria $guiaMensajeria): void
    {
        $this->guia_mensajeria = $guiaMensajeria;

    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void
    {
        $this->response = $response;
    }

    public function getCodeResponse()
    {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void
    {
        $this->code_response = $codeResponse;
    }

    /**
     * Metodo que genera un delivery
     * @param Object $estimate
     * @param TiendaUber $tiendaOrigen
     * @param Collection $placeApiDestino
     * @param BitacoraMensajeriaDestinoTO $destino
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function createDelivery(BitacoraMensajeriaDestinoTO $destino, GuiaMensajeriaTO $guiaMensajeriaTO, $origen)
    {
        try{
            $timestamp = (int) round(Carbon::now()->format('Uu') / pow(10, 6 - 3));

            Log::info("Entra en createDelivery");

            $user = auth()->user();
            $nombreProducto = $guiaMensajeriaTO->getContenido();

            $pesoVolumetrico = number_format(($this->largo * $this->ancho * $this->alto) / 5000, 1, '.', '');
            $pesoVolumetrico = ceil($pesoVolumetrico);
            $data = [
                "orderCode"=> $this->pedido,
                "orderType"=> "100",
                "oldExpressNo"=> "",
                "consignorContact"=> "{$this->clean($origen->getNombre())} {$this->clean($origen->getApellidos())}",
                "consignorPhone"=> "+52".$origen->getTelefono(),
                "consignorCountry"=> "MEX",
                "consignorCity"=> $this->clean( $origen->getMunicipio() ),
                "consignorProvince"=> $this->clean( $origen->getEstado() ),
                "consignorAddress"=>$this->clean( $origen->getReferencias() ),
                "consignorStreet"=> $this->clean( $origen->getDireccionCompuesta() ),
                "consignorZipCode"=> $this->codigo_postal_origen,
                "consigneeContact"=> "{$this->clean($destino->getNombre())} {$this->clean($destino->getApellidos())}",
                "consigneePhone"=> "+52".$destino->getTelefono(),
                "consigneeCountry"=> "MEX",
                "consigneeProvince"=> $this->clean($destino->getEstado()),
                "consigneeCity"=> $this->clean($destino->getMunicipio()),
                "consigneeAddress"=> $this->clean($destino->getReferencias()),
                "consigneeStreet"=> $this->clean($destino->getDireccionCompuesta()),
                "consigneeZipCode"=> $this->codigo_postal_destino,
                "goodsValue"=> $this->valor_paquete,
                "paymentMethod"=> "100",
                "collectingMoney"=> "0",
                "totalCount"=> "1",
                "totalWeight"=> $this->peso,    //kg
                "totalVolume"=> (int) $pesoVolumetrico,    //cm³
                "skuTotal"=> 1,
                "skuName"=> "XX",
                "deliveryRequirements"=> "",
                "batterType"=> "Normal",
                "currency"=> "Local"

            ];

            Log::info("JSON IMILE DELIVERY: ");
            Log::info(json_encode($data));

            $response =  $this->makeRequest($data,"client/order/createB2cOrder");

            Log::info("Crea orden");

            return $response;

        }catch(\Exception $exception){

            Log::info("Error en createDelivery");
            $error = $this->parseError( $exception->getMessage());

            Log::info($error);
            $token = $this->configuracion->get('token');
            $options = [
                "json" => $data,
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ];
            $this->setResponse($error->get('json'));
            $this->setRequest(json_encode($options));
            $this->setCodeResponse($exception->getCode());
            $this->guardaGuiaResponse(($guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO()->getId()));
            throw new \Exception($error->get('message'),$exception->getCode());
        }

    }

    public function validarCampos(){
        $rules = [
        ];

        return $rules;
    }


    /**
     * @return mixed
     */
    public function getGuiaMensajeria()
    {
        return $this->guia_mensajeria;
    }


    // private function generateFile($urlGuia){

    //     Log::info("GenerateFile:");
    //     $token = $this->configuracion->get('token');
    //     try{
    //         $options=[
    //             'connect_timeout' => 90,
    //             'http_errors' => true,
    //             'verify' => false
    //         ];

    //         Log::info("URL PDF:"); Log::info($urlGuia);

    //         $client = new Client();
    //         $response = $client->request('GET', $urlGuia,$options)->getBody()->getContents();

    //         Log::info("RESPONSE PDF:"); Log::info($response);

    //         }catch (\Exception $exception){
    //             Log::info("ERROR peticion PDF:");
    //             Log::info($exception->getMessage());

    //             throw new \Exception($exception->getMessage(),$exception->getCode());
    //         }


    //     return $response;

    // }


    //rastreo guía
    public function rastreoGuia()
    {
        return "aún no estoy vivo";
    }

    public function verificarExcedente($response){
        return "aún no estoy vivo";
    }

    public function buscarGuiasArray(array $arrayGuias)
    {
        return "aún no estoy vivo";
    }

    public function getTipoServicio()
    {
        return "aún no estoy vivo";
    }

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $recoleccion = new \stdClass();
        $recoleccion->mensaje = "Servicio no disponible";
        return $recoleccion;
    }


    public function limitar_cadena($cadena, $limite, $accion){
        $cadena_limpia = $this->clean($cadena);

        if(strlen($cadena_limpia) > $limite){
            if ($accion == 1) {
                return str_split($cadena_limpia, $limite);;
            }
            if ($accion == 2) {
                return substr($cadena_limpia, 0, $limite);
            }

        }

        return $cadena_limpia;
    }

    function clean($cadena) {
       //Reemplazamos la A y a
		$cadena = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª','Ã'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a','a'),
            $cadena
            );

        //Reemplazamos la E y e
        $cadena = str_replace(
            array('É', 'È', 'Ê', 'Ë', 'é', 'è', 'ë', 'ê'),
            array('E', 'E', 'E', 'E', 'e', 'e', 'e', 'e'),
            $cadena );

        //Reemplazamos la I y i
        $cadena = str_replace(
            array('Í', 'Ì', 'Ï', 'Î', 'í', 'ì', 'ï', 'î'),
            array('I', 'I', 'I', 'I', 'i', 'i', 'i', 'i'),
            $cadena );

        //Reemplazamos la O y o
        $cadena = str_replace(
            array('Ó', 'Ò', 'Ö', 'Ô', 'ó', 'ò', 'ö', 'ô'),
            array('O', 'O', 'O', 'O', 'o', 'o', 'o', 'o'),
            $cadena );

        //Reemplazamos la U y u
        $cadena = str_replace(
            array('Ú', 'Ù', 'Û', 'Ü', 'ú', 'ù', 'ü', 'û'),
            array('U', 'U', 'U', 'U', 'u', 'u', 'u', 'u'),
            $cadena );

        //Reemplazamos la N, n, C y c
       $cadena = str_replace(
        array('ñ', 'Ñ', 'ç', 'Ç', 'ï¿½','ÃƒÂ±'),
        array('n', 'N', 'c', 'C', 'n', 'n'),
        $cadena
    );

    //Esta parte se encarga de eliminar cualquier caracter extraño
    $cadena = str_replace(
        array("\\", "¨", "º", "-", "~",
            "#", "@", "|", "!", "\"",
            "·", "$", "%", "&", "/",
            "(", ")", "?", "'", "¡",
            "¿", "[", "^", "`", "]",
            "+", "}", "{", "¨", "´",
            ">", "<", ";", ",", ":",
            ".", "ï¿½",'ÃƒÂ±'),
        '',
        $cadena
    );


        return $cadena;
    }
}
