<?php
namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Mensajerias\Accesos\AccesoCampoMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeria;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaResponse;
use App\ClaroEnvios\Uber\TiendaUber;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\ZPL\Logos;
use App\ClaroEnvios\ZPL\ZPL;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use SKAgarwal\GoogleApi\PlacesApi;
use \Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaBigSmart
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 * @author Roberto Martinez
 */


class MensajeriaBigSmart extends MensajeriaMaster
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
    private $id;
    private $comercioNegociacionID;
    private $custom;
    private $url_tracking;
    private $tienda_nombre;

    private $keys;
    private $tiendaId;
    private $code_response;
    protected $id_configuracion;

    private $comercioID;

    private $labelServicio;

    private $arrayLabelUrl = [
        'PRODUCCION'=>"https://api.bigsmart.mx/api/",
        'TEST'=>"https://demo-api.bigsmart.mx/api/"

    ];

    private $arrayLoginUrl = [
        'PRODUCCION'=>"https://api.bigsmart.mx/api/auth/login",
        'TEST'=>"https://demo-api.bigsmart.mx/api/auth/login"

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
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();
            
            if(!empty($mensajeriaTO->getTiendaNombre())){
                $this->tienda_nombre = $mensajeriaTO->getTiendaNombre();
            }
            if ($mensajeriaTO->getId() == 13) { //si es id 13 caminamos se cambia al id de bigsmart
                $mensajeriaTO->setId(6);
            }
            $this->id = $mensajeriaTO->getId();
            $this->custom = "";
            if(!empty($mensajeriaTO->getCustom())){
                $this->custom = $mensajeriaTO->getCustom();
            }
            $this->comercioNegociacionID = Auth::user()->id;
            $this->comercioNegociacionID = 1;

            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());

            if($mensajeriaTO->getNegociacionId() == 1 || $mensajeriaTO->getNegociacionId() == 4){
                $accesoComercioMensajeriaTO->setComercioId(1);
            }

            $this->configurarAccesos($accesoComercioMensajeriaTO);
//            die(print_r($this));
            if(!$this->configuracion){
                $this->configuracion = collect();
            }
            // $this->configuracion->put('API_KEY_GOOGLE',"AIzaSyDzyX0ZVSyqb9TxYQW9JxVW6Vb3Oc2w47Q");

            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                $this->endpointLogin = $this->arrayLoginUrl['PRODUCCION'];
                $this->url_tracking = "https://bigsmart.mx/tracking/?trackingNumber=";
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->endpointLogin = $this->arrayLoginUrl['TEST'];
                $this->url_tracking = "https://dev.gafa.codes/bigsmart.mx/tracking/?trackingNumber=";
            }

        //    die(print_r($this->configuracion));
        }
    }

    /**
     * Otorga una guia
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO){
        $destino = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $tiendaOrigen = TiendaUber::where('id_tienda',$this->tiendaId)->first();
        $array=[];
        // $placeApiDestino = $this->searchPlaceID($destino);
        // $estimate = $this->createEstimate($tiendaOrigen,$placeApiDestino,$guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId());

        $delivery = $this->createDelivery($destino, $guiaMensajeriaTO, $cotizacion->getTipoServicio());
        //Intenta crear ZPL
        $file =  $this->generateFileZPL($delivery->data->tracking_number, $guiaMensajeriaTO);
        $extension = ".pdf";
        if($guiaMensajeriaTO->getTipoDocumento() == 'zpl'){
            $extension = ".zpl";
        }

        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath.('/'.$delivery->data->tracking_number.'_'.date('YmdHis').'.'.$this->extension_guia_impresion);
        file_put_contents($rutaArchivo, $file['data']);
        $guia = $file['guia'];
        $nombreArchivo = $delivery->data->tracking_number.'_'.date('YmdHis').$extension;
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file['data']) : base64_encode($file['data']);

        $array['guia']=$delivery->data->tracking_number;
        $array['imagen']=$dataFile;
        $array['extension']="pdf";
        $array['nombreArchivo']=$nombreArchivo;
        $array['ruta']=$rutaArchivo;
        $array['link_rastreo_entrega'] = env('TRACKING_LINK_T1ENVIOS')."".$delivery->data->tracking_number;
        // $array['link_rastreo_entrega'] = $this->url_tracking."".$delivery->data->tracking_number;
        $array['location']=(env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION');
        $array['infoExtra']=[
            'codigo'=>101,
            'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
            'identificadorUnico'=>'',
            'tracking_link' => env('TRACKING_LINK_T1ENVIOS')."".$delivery->data->tracking_number
            // 'tracking_link' => $this->url_tracking."".$delivery->data->tracking_number
        ];
        
        return $array;

    }

    /**
     * @param $data
     * @param string $type
     * @param string $method
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function makeRequest($data,$type, $method = 'POST'){

        try{
            
            if($this->configuracion->count() >=2){
                $token = $this->configuracion->get('token');

                if($token) {
                    $options = [
                        "json" => $data,
                        'connect_timeout' => 90,
                        'http_errors' => true,
                        'verify' => false,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $token,
                            'Accept' => 'application/json'
                        ]
                    ];

                    $client = new Client();

                    $response = $client->request($method, $this->endpointLabel . $type, $options);

                    $statusResponse = $response->getStatusCode();
                    $content = $response->getBody()->getContents();
                    //Datos necesario para guardar log
                    $this->setResponse($content);
                    $this->setCodeResponse($statusResponse);
                    $responseLog = json_decode($content);

                    return $responseLog;


                }else{

                    Log::info("Nuevo Token:");
                    $accesoCampo = AccesoCampoMensajeria::with('accesoComercioMensajeria')
                        ->where('mensajeria_id',$this->id)
                        ->where('clave','token')
                        ->first();

                    $token = $this->getToken('client_credentials');
                    $this->guardaToken($token,$accesoCampo);

                    $this->configuracion->put('token',$token);
                    $response= $this->requestBig($data,$type,$method, $token);

                    return $response;

                }
            }else{
                throw new \Exception("No cuenta con credenciales de mensajeria");
            }


        }catch (\Exception $exception){

            Log::error($exception->getMessage());

            if($exception->getCode() == 401){

                Log::info("Regresa 401:");

                $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->id)
                    ->where('clave','token')
                    ->first();

                $accesoComercio = AccesoComercioMensajeria::where('mensajeria_id',$this->id)
                    ->where('comercio_id',$this->comercioNegociacionID)
                    ->where('acceso_campo_mensajeria_id',$accesoCampo->id)
                    ->first();
                if($accesoComercio){
                    $tokenActual = $this->configuracion->get('token');
                    Log::info("Token actual: ".$tokenActual);

                    $tokenBig = $this->getToken('client_credentials');

                    $newToken = $this->actualizaToken($tokenBig);
                    $response= $this->requestBig($data,$type,$method, $newToken);

                    return $response;

                }else{
                    Log::info("Nuevo Token:");
                    $token = $this->getToken('client_credentials');
                    $this->guardaToken($token,$accesoCampo);

                    $this->configuracion->put('token',$token);
                    $response= $this->requestBig($data,$type,$method, $token);

                    return $response;

                }

            }else{
                throw new \Exception($exception->getMessage(),$exception->getCode());
            }
        }
    }

    private function guardaToken($token,$accesoCampo){

        Log::info("Guarda Token:");
        $date = Carbon::now();
        $acceso = new AccesoComercioMensajeria();
        $acceso->acceso_campo_mensajeria_id = $accesoCampo->id;
        $acceso->mensajeria_id = $this->id;
        $acceso->comercio_id = $this->comercioNegociacionID;
        $acceso->valor = $token;
        $acceso->created_at = $date->format('Y-m-d H:i:s');
        $acceso->save();

    }

    private function actualizaToken($token){
        $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->id)
            ->where('clave','token')
            ->first();
        Log::info("Actualiza Token campo id: ".$accesoCampo->id);

        $acceso = AccesoComercioMensajeria::where('acceso_campo_mensajeria_id',$accesoCampo->id)->where('mensajeria_id',$this->id)
            ->where('comercio_id',$this->comercioNegociacionID)
            ->firstOrFail();
        $acceso->valor = $token;
        $acceso->update();

        return $acceso->valor;

    }

    private function getToken($grantType,$token = null){
        try{

            Log::info("Token actual: ".$token);
            $options=[
                "json"=> [
                    "email" => $this->configuracion->get('email'),
                    "password" =>$this->configuracion->get('password')
                ],
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers'  => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json'
                ]
            ];

//            if($token)
//                $options["form_params"]["refresh_token"] = $token;


            $client = new Client();
//            die(print_r($options));
            $response = $client->request("POST", $this->endpointLogin,$options)->getBody()->getContents();
            $result = json_decode($response);
            Log::info("RESPONSE TOKEN:");
            Log::info($response);

            return $result->access_token;

        }catch (\Exception $exception){
            Log::info("ERROR getToken:");
            Log::info($exception->getMessage());
//            die(print_r('aqui'));
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

    private function requestBig($data,$type,$method, $token){

        try{
        // $token = $this->configuracion->get('token');
        $options=[
            "json"=>$data,
            'connect_timeout' => 90,
            'http_errors' => true,
            'verify' => false,
            'headers'  => [
                'Content-Type'=>'application/json',
                'Authorization'=>'Bearer '.$token
            ]
        ];

        $client = new Client();
        $response = $client->request($method, $this->endpointLabel.$type,$options)->getBody()->getContents();
        Log::info("RESPONSE con token:"); Log::info($response);
        $this->setResponse($response);

        return json_decode($response);

        }catch (\Exception $exception){
            Log::info("ERROR peticion:");
            Log::info($exception->getMessage());
//            die(print_r('aqui'));
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
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
    public function createDelivery(BitacoraMensajeriaDestinoTO $destino, GuiaMensajeriaTO $guiaMensajeriaTO, $tipoServicio)
    {
        try{
            $date = Carbon::now()->addDay()->toDateString();
            $datePickup = Carbon::now();

            $origen =  $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
            $this->comercioID = $guiaMensajeriaTO->getComercioId();

            Log::info("Entra en createDelivery");
            $usuario = Auth::user()->id;
            $secondaryC = "";
            Log::info("ID Usuario:". $usuario);
            switch ($usuario) {
                case 9:
                    $secondaryC = "Sears";
                    break;

                case 10:
                    $secondaryC = "Claroshop";
                    break;

                case 11:
                    $secondaryC = "Sanborns";
                    break;

                default:
                    $secondaryC = "Claroshop";
                    break;
            }

            $servicio = "";

            $this->labelServicio = "";

            switch ($tipoServicio) {
                case "EXPRESS":
                    $servicio = "motorcycle";
                    break;

                case "SAME_DAY":
                    $servicio = "car";
                    break;
                
                case "order":
                    $servicio = "van";
                    $this->labelServicio = "NEXT DAY";
                    break;
                
                case "CAMINAMOS":
                    $servicio = "car";
                    $this->labelServicio = "CAMINANDO";
                    break;

                default:
                    $servicio = "car";
                    break;
            }
            Log::info("TIPO SERVICIO:". $servicio);
            
            if(
                in_array($this->comercioID,explode(',',env('COMPANY_SEARS_IDS'))) ||
                in_array($this->comercioID,explode(',',env('COMPANY_CLARO_IDS'))) ||
                in_array($this->comercioID,explode(',',env('COMPANY_SANBORNS_IDS')))
            ){
                Log::info("ES DE TIENDAS");
                $data = [
                    "carrier"=> 'T1 Envios',
                    "primary_client"=> "Grupo Carso",
                    "secondary_client"=> $secondaryC,
                    "shipping_method"=> $servicio,
                    "shipping_option"=> "next_day",
                    "customer_first_name"=> $destino->getNombre(),
                    "customer_last_name"=> $destino->getApellidos(),
                    "customer_mobile_number"=> "+52".$destino->getTelefono(),
                    "customer_email"=> $destino->getEmail(),
                    "customer_street"=> $this->limitar_cadena($destino->getCalle(), 80, ""),
                    "customer_settlement"=> $destino->getColonia(),
                    "customer_external_number"=> $destino->getNumero(),
                    "customer_postcode"=> $this->codigo_postal_destino,
                    "customer_address_reference"=> $destino->getReferencias(),
                    "customer_delivery_start_date"=> $date,
                    "customer_delivery_end_date"=> $date,
                    "purchase_order_number"=> $this->pedido,
                    "weight"=> $this->peso,
                    "amount" => $this->valor_paquete,
                    "pickup_full_name"=>"{$origen->getNombre()} {$origen->getApellidos()}",
                    "pickup_description"=>"",
                    "pickup_phone_number"=>"+52".$origen->getTelefono(),
                    "pickup_email"=> $origen->getEmail(),
                    "pickup_street"=>$origen->getDireccionCompuesta(),
                    "pickup_settlement"=>$origen->getColonia(),
                    "pickup_external_number"=>$origen->getNumero(),
                    "pickup_internal_number"=>"",
                    "pickup_postcode"=> $this->codigo_postal_origen,
                    "pickup_address_reference"=>$origen->getReferencias(),
                    "pickup_latitude"=> "",
                    "pickup_longitude"=> "",
                    "pickup_start_datetime"=> $datePickup->format('Y-m-d H:i'),
                    "pickup_end_datetime"=> $datePickup->endOfDay()->format('Y-m-d H:i'),
                    "location_id"=> $this->tiendaId,
                    "location_name"=>$this->tienda_nombre,
                    "location_type"=>"Tienda"
                ];
            }
            else{
                Log::info("SELFSERVICE");
                $data = [
                    "carrier"=> 'T1 Envios',
                    "primary_client"=> "Grupo Carso",
                    "secondary_client"=> $secondaryC,
                    "shipping_method"=> $servicio,
                    "shipping_option"=> "next_day",
                    "customer_first_name"=> $destino->getNombre(),
                    "customer_last_name"=> $destino->getApellidos(),
                    "customer_mobile_number"=> "+52".$destino->getTelefono(),
                    "customer_email"=> $destino->getEmail(),
                    "customer_street"=> $this->limitar_cadena($destino->getCalle(), 80, ""),
                    "customer_settlement"=> $destino->getColonia(),
                    "customer_external_number"=> $destino->getNumero(),
                    "customer_postcode"=> $this->codigo_postal_destino,
                    "customer_address_reference"=> $destino->getReferencias(),
                    "customer_delivery_start_date"=> $date,
                    "customer_delivery_end_date"=> $date,
                    "purchase_order_number"=> $this->pedido,
                    "weight"=> $this->peso,
                    "amount" => $this->valor_paquete
                ];
            }

            $this->setRequest(json_encode($data));
          
            $response =  $this->makeRequest($data,"orders-external");

            Log::info("Crea orden: ".$response->data->id);
            return $response;

        }catch (\Exception $exception){
            Log::info("ERROR Generar guía:");
            Log::info($exception->getMessage());
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }

    }


    private function generateFileZPL($noGuia, $guiaMensajeriaTO){

        $ZPL = new ZPL();
        $zplResult='';
        $cadena='';

        $token = $this->configuracion->get('token');
        try{
            $options=[
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers'  => [
                    'Content-Type'=>'application/json',
                    'Authorization'=>'Bearer '.$token
                ]
            ];

            Log::info("GUIA ZPL:"); Log::info($noGuia);

            $client = new Client();
            $response = $client->request('GET', $this->endpointLabel."orders/".$noGuia."/zpl-label",$options)->getBody()->getContents();
            // Log::info("RESPONSE ZPL:"); Log::info($response);
           
            }catch (\Exception $exception){
                Log::info("ERROR peticion ZPL:");
                Log::info($exception->getMessage());
                if($exception->getCode() == 401){

                    Log::info("Regresa 401:");
    
                    $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->id)
                        ->where('clave','token')
                        ->first();
    
                    $accesoComercio = AccesoComercioMensajeria::where('mensajeria_id',$this->id)
                        ->where('comercio_id',$this->comercioNegociacionID)
                        ->where('acceso_campo_mensajeria_id',$accesoCampo->id)
                        ->first();
                    if($accesoComercio){
                        $tokenActual = $this->configuracion->get('token');
                        Log::info("Token actual: ".$tokenActual);
    
                        $tokenBig = $this->getToken('client_credentials');
    
                        $newToken = $this->actualizaToken($tokenBig);

                        $options=[
                            'connect_timeout' => 90,
                            'http_errors' => true,
                            'verify' => false,
                            'headers'  => [
                                'Content-Type'=>'application/json',
                                'Authorization'=>'Bearer '.$newToken
                            ]
                        ];
                        $response = $client->request('GET', $this->endpointLabel."orders/".$noGuia."/zpl-label",$options)->getBody()->getContents();
    
                    }else{
                        Log::info("Nuevo Token:");
                        $token = $this->getToken('client_credentials');
                        $this->guardaToken($token,$accesoCampo);
    
                        $this->configuracion->put('token',$token);
                        $options=[
                            'connect_timeout' => 90,
                            'http_errors' => true,
                            'verify' => false,
                            'headers'  => [
                                'Content-Type'=>'application/json',
                                'Authorization'=>'Bearer '.$token
                            ]
                        ];
                        $response = $client->request('GET', $this->endpointLabel."orders/".$noGuia."/zpl-label",$options)->getBody()->getContents();
    
    
                    }
    
                }else{
                    throw new \Exception($exception->getMessage(),$exception->getCode());
                }
            }

        if($response){
            $ruta ="NA";
            if(!empty($this->custom)){
                foreach ($this->custom as $key => $value) {
                    if (is_array($value) && empty($value)) {
                        $ruta = "NA";
                    }  
                    else if ($value["nombre"] == "ruta") {
                        $ruta = $value["valor"];
                    }

                }
            }

            $file = '';

            $qr= strpos($response, '^FDQA');
            $response = trim($response); //se eliminan espacios
            $finarchivo= strpos($response, '^XZ');
            // $response = substr_replace($response, '^FS',$qr+30, $finarchivo);
            $response = substr_replace($response, '^FS^XZ',$finarchivo, $finarchivo);
            $finarchivo= strpos($response, '^XZ');

            $cadena = "^CF0,70
                ^FO400,1490^FD".$ruta."^FS
                ^FO50,1500^FD".$this->labelServicio."^FS^XZ"
                ;
            $file = substr_replace($response, $cadena,$finarchivo, $finarchivo);

            $file = $this->changeImagen($file);
           
            try{

                if(isset($file) && !empty($file) && $guiaMensajeriaTO->getTipoDocumento() != 'zpl'){
                    $zplResult = $ZPL->convertirZPL($file,"pdf", $noGuia);

                    if($zplResult['success'] == false){
                        Log::info("Fallo zpl, se genera pdf normal");
                        $zplResult = $this->descargaPDF($noGuia);
                    }
                }
                else{
                    Log::info("ZPL vacío, se genera pdf normal");
                    $zplResult = $this->descargaPDF($noGuia);
                }
                
                  
               

                
            }catch (\Exception $exception){
                Log::info("Fallo zpl, se genera pdf normal");
                $zplResult = $this->descargaPDF($noGuia);
            }

        }

        return $zplResult;

    }

    private function descargaPDF($noGuia)
    {
        Log::info("Entra en descargaPDF");
        $token = $this->configuracion->get('token');
        try{
            $options=[
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers'  => [
                    'Content-Type'=>'application/json',
                    'Authorization'=>'Bearer '.$token
                ]
            ];

            Log::info("GUIA PDF:"); Log::info($noGuia);
            $client = new Client();
            $response = $client->request('GET', $this->endpointLabel."orders/".$noGuia."?populate=true&label=true",$options)->getBody()->getContents();
          
            $pdfContent = file_get_contents($response);

            return ['success' => true, 'guia' => $noGuia, 'data' => $pdfContent];
            
        }catch (\Exception $exception){
            Log::info("ERROR peticion PDF:");
            Log::info($exception->getMessage());

            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }

    public function changeImagen($zpl)
    {
        Log::info("Entra en changeImagen");
        $file='';

        $logo = new Logos();
        $logo = $logo->getBigsmartLogo($this->comercioID);
        $file = str_replace("T1 Envios", "", $zpl);
        $file = substr_replace($file, $logo, 123, 2971);
     
        return $file;
    }

    public function getCodeResponse()
    {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void
    {
        $this->code_response = $codeResponse;
    }


    public function limitar_cadena($cadena, $limite, $sufijo){
        $cadena_limpia = $this->clean($cadena);
        if(strlen($cadena_limpia) > $limite){
            return substr($cadena_limpia, 0, $limite) . $sufijo;
        }

        return $cadena_limpia;
    }

    function clean($cadena) {
       //Reemplazamos la A y a
		$cadena = str_replace(
            array('Á', 'À', 'Â', 'Ä', 'á', 'à', 'ä', 'â', 'ª'),
            array('A', 'A', 'A', 'A', 'a', 'a', 'a', 'a', 'a'),
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
            array('Ñ', 'ñ', 'Ç', 'ç'),
            array('N', 'n', 'C', 'c'),
            $cadena
            );

        return $cadena;
    }

}
