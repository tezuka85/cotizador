<?php
namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Uber\TiendaUber;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\CartaPorte\CartaPorteT0;
use App\ClaroEnvios\Mensajerias\ProductoCotizacion\ProductoCotizacion;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\ZPL\ZPL;
use App\Exceptions\ValidacionException;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use \Illuminate\Support\Facades\Log;
use Psy\Command\DumpCommand;
use stdClass;

/**
 * Class MensajeriaUps
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 * @author Roberto Martinez
 */

class MensajeriaUps extends MensajeriaMaster implements MensajeriaCotizable
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
    private $ID;

    private $keys;
    private $tiendaId;
    private $code_response;

    protected $id_configuracion; 
    protected $costo_seguro;
    protected $negociacion;

    private $arrayLabelUrl = [
        'PRODUCCION'=>"https://onlinetools.ups.com/ship/v1/",
        'TEST'=>"https://wwwcie.ups.com/ship/v1/"

    ];

    private $arrayLoginUrl = [
        'PRODUCCION'=>"https://onlinetools.ups.com/ship/v1/shipments/labels",
        'TEST'=>"https://wwwcie.ups.com/ship/v1/shipments/labels"

    ];

    use AccesoConfiguracionMensajeria;


    public function __construct($mensajeriaTO = false)
    {

        if ($mensajeriaTO instanceof MensajeriaTO) {
            $this->location = env('API_LOCATION', 'test');

            $this->costo = $mensajeriaTO->getCosto();
            $this->porcentaje = $mensajeriaTO->getPorcentaje();
            $this->porcentaje_seguro = $mensajeriaTO->getPorcentajeSeguro();
            $this->costo_seguro = $mensajeriaTO->getCostoSeguro();
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
            $this->negociacion = $mensajeriaTO->getNegociacion();
            
            if(!empty($mensajeriaTO->getTiendaNombre())){
                $this->tienda_nombre = $mensajeriaTO->getTiendaNombre();
            }
            $this->ID = $mensajeriaTO->getId();
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

            if(!$this->configuracion){
                $this->configuracion = collect();
            }
            // $this->configuracion->put('API_KEY_GOOGLE',"AIzaSyDzyX0ZVSyqb9TxYQW9JxVW6Vb3Oc2w47Q");

            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                $this->endpointLogin = $this->arrayLoginUrl['PRODUCCION'];
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->endpointLogin = $this->arrayLoginUrl['TEST'];
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
        $origen = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $array=[];

        $delivery = $this->createDelivery($destino, $guiaMensajeriaTO, $cotizacion->getTipoServicio());
        $guia="";
        $file = [];
        //valida la existencia de la guia
        if (isset($delivery->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber)) {
            $guia = $delivery->ShipmentResponse->ShipmentResults->ShipmentIdentificationNumber;
        }
        else{
            Log::info("No retorno de guia");
            //dd($delivery->response->errors['0']->message);
            throw new ValidacionException('UPS Error: '.$delivery->response->errors['0']->message);
        }
        //valida que la etiqueta venga en un array 
        if (is_array($delivery->ShipmentResponse->ShipmentResults->PackageResults)) {
            $packages = $delivery->ShipmentResponse->ShipmentResults->PackageResults;
            $etiquetas = [];
            foreach($packages as $package){
               
                if(isset($package->ShippingLabel->GraphicImage)){
                    $etiqueta = $package->ShippingLabel->GraphicImage;
                   array_push($etiquetas,$etiqueta);
                    
                }
            }
        
            $file =  $this->generateFileZPL($guia,$etiquetas,$destino,$origen);

        }
        else if (isset($delivery->ShipmentResponse->ShipmentResults->PackageResults->ShippingLabel->GraphicImage)) {
           
            $etiqueta = $delivery->ShipmentResponse->ShipmentResults->PackageResults->ShippingLabel->GraphicImage;
            //Intenta crear ZPL
            $file =  $this->generateFileZPL($guia,$etiqueta,$destino,$origen);
        }
        else{
            //Intenta crear PDF
            $file = $this->generateFileZPL($guia);
        }
       
        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath.('/'.$guia.'_'.date('YmdHis').'.'.$this->extension_guia_impresion);
        file_put_contents($rutaArchivo, $file['data']);
        $nombreArchivo = $guia.'_'.date('YmdHis').'.pdf';
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file['data']) : base64_encode($file['data']);

        $array['guia']=$guia;
        $array['imagen']=$dataFile;
        $array['extension']="pdf";
        $array['nombreArchivo']=$nombreArchivo;
        $array['ruta']=$rutaArchivo;
        $array['link_rastreo_entrega'] = env('TRACKING_LINK_T1ENVIOS')."".$guia;
        $array['location']=(env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION');
        $array['infoExtra']=[
            'codigo'=>"M",
            'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
            'identificadorUnico'=>'',
            'tracking_link' => env('TRACKING_LINK_T1ENVIOS')."".$guia
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
            if($this->configuracion->count() >=3){
                $username = $this->configuracion->get('username');
                $password = $this->configuracion->get('password');
                $accessLicenseNumber = $this->configuracion->get('accessLicenseNumber');
               
                if($username && $password && $accessLicenseNumber){ 
                  
                    $options = [
                        "json" => $data,
                        'connect_timeout' => 90,
                        'http_errors' => true,
                        'verify' => false,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Username' => $username,
                            'Password' => $password,
                            'AccessLicenseNumber' => $accessLicenseNumber
                        ]
                    ];
                    
                    
                     Log::info('Request UPS'.json_encode($options));
                   
                    $client = new Client();
                    $response = $client->request($method, $this->endpointLabel . $type, $options);
                    
                    $statusResponse = $response->getStatusCode();
                    $content = $response->getBody()->getContents();
                    //Datos necesario para guardar log
                    $this->setResponse($content);
                    $this->setRequest(json_encode($options));
                    $this->setCodeResponse($statusResponse);

                    $responseLog = json_decode($content);
                   
                   // return $responseLog;
                
                }else{

                    Log::info("No cuenta con credenciales de mensajeria");
                    throw new \Exception("No cuenta con credenciales de mensajeria");
                }
            }else{
                throw new \Exception("No cuenta con credenciales de mensajeria");
            }
        }catch (\GuzzleHttp\Exception\RequestException  $exception) {
            Log::error("ClientErrorResponseException UPS: ".$exception->getMessage().' '.$exception->getFile().' '.$exception->getLine());
            $error = $exception->getResponse();
            $jsonBody = (string) $error->getBody();
            $responseLog = json_decode($jsonBody);
            
             //Datos necesario para guardar log
            //  $this->setResponse($jsonBody);
            //  $this->setRequest( json_encode($options));
            //  $this->setCodeResponse($exception->getCode());
        }catch (\Exception $exception){
            Log::error("Error UPS: ".$exception->getMessage().' '.$exception->getFile().' '.$exception->getLine());
            // $this->setResponse($exception->getMessage());
            // $this->setRequest( json_encode($options));
            // $this->setCodeResponse($exception->getCode());
            $responseLog = json_encode($exception->getMessage());
        }
        Log::info('RESPONSE RATE UPS: ' . $this->getResponse());

        //die(print_r($responseLog));
        return $responseLog;
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

    public function rate($traerResponse= false, $planTarifario = null){

        $tarificador = new stdClass();
        
        $tarificador->success = true;
        $tarificador->message = Response::$messages['successfulSearch'];

        $responseStandar = $this->ratePeticionStandar();
        $responseSaver = $this->ratePeticionSaver();
        
        $dataSaver = $responseSaver['response'];
        $dataStandar = $responseStandar['response'];
        if( property_exists($dataSaver,'response') && property_exists($dataStandar,'response')){
            if( property_exists($dataSaver->response,'errors') && property_exists($dataStandar->response,'errors')){
                $tarificador->success = false;
                $tarificador->message = $dataSaver->response->errors[0]->message." ".$dataStandar->response->errors[0]->message;
                $tarificador->code_response = 400;
                $tarificador->servicios = new stdClass();
             
            }

        }else if (property_exists($dataSaver, 'RateResponse') || property_exists($dataStandar, 'RateResponse')){

            $data = new stdClass();
            $data->standar = $dataStandar;
            $data->saver = $dataSaver;
            foreach ($data as $servicio => $valores) {
                Log::info('DATA RATE');
                Log::info(json_encode($valores));

                $data = json_decode(json_encode($valores));
                
                Log::info('Service type');
                Log::info($servicio);
             
                if( property_exists($valores, 'RateResponse')){
                
                    $tarificador->success = true;
                    $tarificador->message = Response::$messages['successfulSearch'];
                    $dataRateResponse =  $valores->RateResponse;
                
                    Log::info("Servicio: ".$dataRateResponse->RatedShipment->Service->Code);
                    if( property_exists($dataRateResponse->RatedShipment,'NegotiatedRateCharges')){
                        Log::info("NegotiatedRateCharges existente");
                        $costo = round($dataRateResponse->RatedShipment->NegotiatedRateCharges->TotalCharge->MonetaryValue, 2);
                    }
                    else{
                        Log::info("NegotiatedRateCharges no existente");
                        $costo = round($dataRateResponse->RatedShipment->TotalCharges->MonetaryValue, 2);
                    }

                    if (!property_exists($tarificador, 'servicios')) {
                        Log::info('No existe servicios');
                        $tarificador->servicios = new stdClass();
                    }
                    $costoAdicional = 0;
                    $costoSeguro = 0;

                    if ($this->costo != 0) {
                        $costoAdicional = $this->costo;
                        Log::info(' Costo margen: '.$this->costo);
                    } elseif ($this->porcentaje != 0) {
                        $costoAdicional = round($costo * ($this->porcentaje / 100), 2);
                        Log::info(' Porcentaje margen: '.$this->porcentaje);
                    }
                    
                    Log::info(' Costo guia mensajeria UPS: '.$costo);
            
                    if (in_array($this->id_configuracion, array_merge(ConfiguracionComercio::$comerciosZonas,[2,9])) ) {
                        Log::info(' Calculo zonas/prepago: ');

                        $costoGuia = $costo;
                        $costoTotalCalculado = round(($costoGuia /(1-($this->porcentaje/100))) , 2);
                        Log::info(' Costo Total zonas/prepago: ' . $costoTotalCalculado);

                    }else {
                        Log::info(' Calculo default: ');
                        $costoSeguro = $this->seguro ? round($this->valor_paquete * ($this->porcentaje_seguro / 100), 2) : 0;
                        Log::info(' Costo adicional calculado: '.$costoAdicional);
                        Log::info(' Costo Seguro ' . $costoSeguro);
                        $costoTotalCalculado = round($costo + $costoAdicional + $costoSeguro, 2);
                        Log::info(' Costo Total: ' . $costoTotalCalculado);
                    }

                    $servicioNombre = ($dataRateResponse->RatedShipment->Service->Code == '65') ? 'UPS_SAVER' : 'UPS_STANDAR';
                    $servicioMensajeria =  $this->obtenerServicioMensajeria('UPS',$servicioNombre);
                    $date = (isset($dataRateResponse->RatedShipment->ScheduledDeliveryDate)) ? $dataRateResponse->RatedShipment->ScheduledDeliveryDate : Carbon::now()->addDay();
                    $fechaEntrega = new Carbon($date);
    
                    $servicio = $this->responseService($costo,$costo,$costoTotalCalculado,  $servicioMensajeria, $fechaEntrega,$costoSeguro);
                  
                    $tarificador->servicios->{$servicioNombre}  = $servicio;
                    $tarificador->location = $this->location;
                    $tarificador->code_response = $this->code_response;
                 
                    
                }
            }
            
            if ($traerResponse) {
                $tarificador->request = json_encode($data);
                $tarificador->response = $this->getResponse();
                $tarificador->code_response = $this->getCodeResponse();
            }
            
         
        }else{
            $tarificador->success = false;
            $tarificador->message = $dataSaver->Response->ResponseStatus->Description." ".$dataStandar->Response->ResponseStatus->Description;
            $tarificador->code_response = 400;
            $tarificador->servicios = new stdClass();
       

        }
       
        Log::info('termina cotizacion UPS');
        return $tarificador;
    }

    private function ratePeticionSaver()
    {
        Log::info("Rate peticion UPS Saver");

        $shipperNumber = $this->configuracion->get('shipperNumber');
        $pesoVolumetrico = round(($this->largo * $this->ancho * $this->alto) / 5000);
        if ($pesoVolumetrico == 0) $pesoVolumetrico = 1;

        $pesoACotizar = $this->peso > $pesoVolumetrico ? $this->peso : $pesoVolumetrico;

        $data = [
            "RateRequest" => [
                "Shipment" => [
                    "ShipmentRatingOptions" => [
                        "UserLevelDiscountIndicator" => "TRUE",
                        "NegotiatedRatesIndicator"=>""
                    ],
                    "Shipper" => [
                        "Name" => "T1Envios",
                        "ShipperNumber" => $shipperNumber,
                        "Address" => [
                            "StateProvinceCode" => $this->siglas_codigo_postal_origen,
                            "PostalCode" => "$this->codigo_postal_origen",
                            "CountryCode" => "MX"
                        ]
                    ],
                    "ShipTo" => [
                        "Address" => [
                            "StateProvinceCode" => $this->siglas_codigo_postal_destino,
                            "PostalCode" => "$this->codigo_postal_destino",
                            "CountryCode" => "MX"
                        ]
                    ],
                    "ShipFrom" => [
                        "Address" => [
                            "StateProvinceCode" => $this->siglas_codigo_postal_origen,
                            "PostalCode" => "$this->codigo_postal_origen",
                            "CountryCode" => "MX"
                        ]
                    ],
                    "Service" => [
                        "Code" => "65", 
                        "Description" => "Saver"
                    ],
                    "ShipmentTotalWeight" => [
                        "UnitOfMeasurement" => [
                            "Code" => "KGS",
                            "Description" => "Kilograms"
                        ],
                        "Weight" =>  "$pesoACotizar"
                    ],
                    "Package" => [
                        "PackagingType" => [
                            "Code" => "02",
                            "Description" => "Package"
                        ],
                        "Dimensions" => [
                            "UnitOfMeasurement" => ["Code" => "CM"],
                            "Length" => "$this->largo",
                            "Width" => "$this->ancho",
                            "Height" => "$this->alto"
                        ],
                        "PackageWeight" => [
                            "UnitOfMeasurement" => [
                                "Code" => "KGS"
                            ],
                            "Weight" => "$pesoACotizar"
                        ]
                    ]
                ]
            ]
        ];
         if($this->seguro == true || $this->id_configuracion == 1){
             Log::info('Solicita seguro RATE UPS');
             $data["RateRequest"]['Shipment']['Package']["PackageServiceOptions"] = [
                     "DeclaredValue" => [
                         "Type" => [
                             "Code" => "01"
                         ],
                         "CurrencyCode" => "MXN",
                         "MonetaryValue" => "$this->valor_paquete"
                     ]
                 ];
         }
         $response =  $this->makeRequest($data, "rating/Rate");
      
        return [
            'request'=>$data,
            'response'=>$response
        ];
    }

    private function ratePeticionStandar()
    {
        Log::info("Rate peticion UPS Standar");

        $shipperNumber = $this->configuracion->get('shipperNumber');
        $pesoVolumetrico = round(($this->largo * $this->ancho * $this->alto) / 5000);
        if ($pesoVolumetrico == 0) $pesoVolumetrico = 1;

        $pesoACotizar = $this->peso > $pesoVolumetrico ? $this->peso : $pesoVolumetrico;

        $data = [
            "RateRequest" => [
                "Shipment" => [
                    "ShipmentRatingOptions" => [
                        "UserLevelDiscountIndicator" => "TRUE",
                        "NegotiatedRatesIndicator"=>""
                    ],
                    "Shipper" => [
                        "Name" => "T1Envios",
                        "ShipperNumber" => $shipperNumber,
                        "Address" => [
                            "StateProvinceCode" => $this->siglas_codigo_postal_origen,
                            "PostalCode" => "$this->codigo_postal_origen",
                            "CountryCode" => "MX"
                        ]
                    ],
                    "ShipTo" => [
                        "Address" => [
                            "StateProvinceCode" => $this->siglas_codigo_postal_destino,
                            "PostalCode" => "$this->codigo_postal_destino",
                            "CountryCode" => "MX"
                        ]
                    ],
                    "ShipFrom" => [
                        "Address" => [
                            "StateProvinceCode" => $this->siglas_codigo_postal_origen,
                            "PostalCode" => "$this->codigo_postal_origen",
                            "CountryCode" => "MX"
                        ]
                    ],
                    "Service" => [
                        "Code" => "11", 
                        "Description" => "Standar"
                    ],
                    "ShipmentTotalWeight" => [
                        "UnitOfMeasurement" => [
                            "Code" => "KGS",
                            "Description" => "Kilograms"
                        ],
                        "Weight" =>  "$pesoACotizar"
                    ],
                    "Package" => [
                        "PackagingType" => [
                            "Code" => "02",
                            "Description" => "Package"
                        ],
                        "Dimensions" => [
                            "UnitOfMeasurement" => ["Code" => "CM"],
                            "Length" => "$this->largo",
                            "Width" => "$this->ancho",
                            "Height" => "$this->alto"
                        ],
                        "PackageWeight" => [
                            "UnitOfMeasurement" => [
                                "Code" => "KGS"
                            ],
                            "Weight" => "$pesoACotizar"
                        ]
                    ]
                ]
            ]
        ];
       // die(var_dump($this->seguro));
        if($this->seguro == true || $this->id_configuracion == 1){
            Log::info('Solicita seguro RATE UPS');
            $data["RateRequest"]['Shipment']['Package']["PackageServiceOptions"] = [
                    "DeclaredValue" => [
                        "Type" => [
                            "Code" => "01"
                        ],
                        "CurrencyCode" => "MXN",
                        "MonetaryValue" => "$this->valor_paquete"
                    ]
                ];
        }
        //dd(json_encode($data));
        $response =  $this->makeRequest($data, "rating/Rate");
        
        return [
            'request'=>$data,
            'response'=>$response
        ];
    }

    public function validarCampos() {

    }

    public function getTipoServicio() {

    }

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO, $guia = '') {
    }

    public function verificarExcedente($response) {

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
            
            $origen =  $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
            $shipperNumber = $this->configuracion->get('shipperNumber');
            $claveSat = $guiaMensajeriaTO->getClaveProductoSAT() != '' ? $guiaMensajeriaTO->getClaveProductoSAT() : '31181701';
            
            //$claveSat = $guiaMensajeriaTO->getClaveProductoSAT();
          
            $descSat = "(".$claveSat.") ".$guiaMensajeriaTO->getContenido();
            $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
            
            //Variables para carta  porte y productos
            $productosCotizacion = ProductoCotizacion::where('id_bitacora_cotizacion',$bitacoraCotizacionMensajeriaTO->getId())->get()->toArray();
         
            if (count($productosCotizacion) > 0) {
                $cartaPorteT0 = new CartaPorteT0();
                $cartaPorteT0->setProductos($productosCotizacion);
                $guiaMensajeriaTO->setCartaPorteTO($cartaPorteT0);
            }

            $cartaPorte = $guiaMensajeriaTO->getCartaPorteTO();
            $productos =  ($cartaPorte!=null && !empty($cartaPorte))?$cartaPorte->getProductos():[];
           
            if (count($productos) != 0) {

                foreach ($productos as $key => $producto) {

                    $descSat = "(" . $producto['codigo_sat'] . ") " . $producto['descripcion_sat'];
                    $referenceNumber = "31181701";
                    if ($this->pedido != null && $this->pedido != "") {
                        $referenceNumber = $this->pedido;
                    } else {
                        if ($producto['codigo_sat'] != null && $producto['codigo_sat'] != "") {
                            $referenceNumber = $producto['codigo_sat'];
                        }
                    }

                    $arrayProductos[$key] =  [
                        "Description" => $this->limitar_cadena($producto['descripcion_sat'], 35, ""), //CAMBIAR POR CARTA PORTE
                        "UnitPrice" => $producto['precio'],
                        "Packaging" => [
                            "Code" => "02",
                            "Description" => "Paquete en caja",
                        ],
                        "Dimensions" => [
                            "UnitOfMeasurement" => [
                                "Code" => "CM"
                            ],
                            "Length" => strval((int)$producto['largo']),
                            "Width" => strval((int)$producto['ancho']),
                            "Height" => strval((int)$producto['alto'])
                        ],
                        "PackageWeight" => [
                            "UnitOfMeasurement" => [
                                "Code" => "KGS"
                            ],
                            "Weight" => strval((int)$producto['peso'])
                        ],
                        "ReferenceNumber" => [
                            [
                                "Value" => $referenceNumber
                            ],
                            [
                                "Value" => $this->limitar_cadena($descSat, 35, ""), //DESCRIPCION DEL PRODUCTO PARA CARTA PORTE
                            ]

                        ]
                    ];

                    if ($bitacoraCotizacionMensajeriaTO->getSeguro()) {
                        Log::info('Solicita seguro Guia UPS productos '.$producto['codigo_sat']);
                        $arrayProductos[$key]["PackageServiceOptions"] = [
                            "DeclaredValue" => [
                                "Type" => [
                                    "Code" => "01"
                                ],
                                "CurrencyCode" => "MXN",
                                "MonetaryValue" => "{$producto['precio']}"
                            ]
                        ];
                    }
                }
              
            } else {
           // die(print_r($bitacoraCotizacionMensajeriaTO->getValorPaquete()));
           $valorPaquete = $bitacoraCotizacionMensajeriaTO->getValorPaquete();
                $arrayProductos[0] =  [
                    "Description" => $this->limitar_cadena($guiaMensajeriaTO->getContenido(), 35, ""), //CAMBIAR POR CARTA PORTE
                    "UnitPrice" => "$valorPaquete",
                    "Packaging" => [
                        "Code" => "02",
                        "Description" => "Paquete en caja",
                    ],
                    "Dimensions" => [
                        "UnitOfMeasurement" => [
                            "Code" => "CM"
                        ],
                        "Length" => strval((int)$this->largo),
                        "Width" => strval((int)$this->ancho),
                        "Height" => strval((int)$this->alto)
                    ],
                    "PackageWeight" => [
                        "UnitOfMeasurement" => [
                            "Code" => "KGS"
                        ],
                        "Weight" => strval((int)$this->peso)
                    ],
                    "ReferenceNumber" => [
                        [
                            "Value" => ($this->pedido != null && $this->pedido != "") ? $this->pedido : $claveSat
                        ],
                        [
                            "Value" => $this->limitar_cadena($descSat, 35, ""), //DESCRIPCION DEL PRODUCTO PARA CARTA PORTE
                        ]

                    ]
                ];
                //die(var_dump( $bitacoraCotizacionMensajeriaTO->getSeguro() ));
                $seguro = ($this->id_configuracion == 1) ? 1 : $bitacoraCotizacionMensajeriaTO->getSeguro();
                if ($seguro == 1) {
                    Log::info('Solicita seguro Guia UPS sin productos');
                    $arrayProductos[0]["PackageServiceOptions"] = [
                        "DeclaredValue" => [
                            "Type" => [
                                "Code" => "01"
                            ],
                            "CurrencyCode" => "MXN",
                            "MonetaryValue" => "$valorPaquete"
                        ]
                    ];
                }
            }
            
            $rfc = ($this->id_configuracion == 1) ? env('RFC_CS') : $guiaMensajeriaTO->getRFC();
           // die(print_r($rfc));
            $data = [
                "ShipmentRequest"=>[
                    "Shipment"=>[
                        "Shipper"=>[
                            "Name"=>"T1 Envios",
                            "AttentionName"=> $this->limitar_cadena($origen->getNombre()." ".$origen->getApellidos(),35,""),
                            "TaxIdentificationNumber"=>$rfc,
                            "Phone"=>[
                                "Number"=>"+52".$origen->getTelefono(),
                                "Extension"=>""
                            ],
                            "ShipperNumber"=>$shipperNumber,
                            "Address"=>[
                                "AddressLine"=>$this->limitar_cadena($origen->getDireccionCompuesta(), 35, ""),
                                "City"=>$this->limitar_cadena($origen->getEstado(), 30, ""),
                                "StateProvinceCode"=>$this->limitar_cadena($origen->getMunicipio(),5,""),
                                "PostalCode"=>$this->codigo_postal_origen,
                                "CountryCode"=>"MX"
                            ]
                        ],
                        "ShipTo"=>[
                            "Name"=>$this->limitar_cadena($destino->getNombre()." ".$destino->getApellidos(), 35, ""),
                            "AttentionName"=>$this->limitar_cadena($destino->getNombre()." ".$destino->getApellidos(), 35, ""),
                            "Phone"=>[
                                "Number"=>"+52".$destino->getTelefono()
                            ],
                            "TaxIdentificationNumber"=>"",
                            "Address"=>[
                                "AddressLine"=> $this->limitar_cadena($destino->getDireccionCompuesta(), 35, ""),
                                "City"=>$this->limitar_cadena($destino->getEstado(), 30, ""),
                                "StateProvinceCode"=>$this->limitar_cadena($destino->getMunicipio(),5,""),
                                "PostalCode"=>$this->codigo_postal_destino,
                                "CountryCode"=>"MX"
                            ]
                        ],
                        "ShipFrom"=>[
                            "Name"=>"T1 Envios",
                            "AttentionName"=>$this->limitar_cadena($origen->getNombre()." ".$origen->getApellidos(),35,""),
                            "Phone"=>[
                                "Number"=>"+52".$origen->getTelefono()
                            ],
                            "TaxIdentificationNumber"=>"",
                            "Address"=>[
                                "AddressLine"=>$this->limitar_cadena($origen->getDireccionCompuesta(), 35, ""),
                                "City"=>$this->limitar_cadena($origen->getEstado(), 30, ""),
                                "StateProvinceCode"=>$this->limitar_cadena($origen->getMunicipio(),5,""),
                                "PostalCode"=>$this->codigo_postal_origen,
                                "CountryCode"=>"MX"
                            ]
                        ],
                        "Service"=>[
                            "Code"=> $tipoServicio == 'UPS_SAVER' ? "65" : "11",
                            "Description"=>""
                        ],
                        "Package"=>$arrayProductos,
                        "PaymentInformation"=>[
                            "ShipmentCharge"=>[
                                "Type"=>"01",
                                "BillShipper"=>[
                                    "AccountNumber"=>$shipperNumber
                                ]
                            ]
                        ]
                    ],
                    "LabelSpecification"=>[
                        "LabelImageFormat"=>[
                            "Code"=>"ZPL",
                            "Description"=>"LabelImageFormat Description"
                        ],
                        "LabelStockSize"=>[
                            "Height"=> "8",
                            "Width"=> "4"
                        ],
                        "HTTPUserAgent"=>"Mozilla/4.5"
                    ]
                ]
            ];
           
            $response =  $this->makeRequest($data,"shipments");
           
            return $response;

        }catch (\Exception $exception){
            Log::info("ERROR Generar guía:");
            Log::info($exception->getMessage());
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }

    }
    
    private function generateFileZPL($noGuia, $etiqueta="",$destino="",$origen=""){

        $ZPL = new ZPL();
        $zplResult='';
        $zplCode='';
        
        //valida si etiqueta es un array
        if (is_array($etiqueta)) {
            $textEtiqueta="";
          
            foreach($etiqueta as $etiquetaItem){
                $etiquetaItem = base64_decode($etiquetaItem);
                $textEtiqueta = $this->agregarQR($etiquetaItem, $noGuia);
                $zplCode = $zplCode.$textEtiqueta;
            }
            $zplResult = $ZPL->convertirZPL($zplCode,"pdf", $noGuia);

            return $zplResult;
        }

        // SI ESTÁ VACIO EL ZPL HACER PETICIÓN, SINO SOLO GENERAR EL ARCHIVO

        if ($etiqueta !== ""){
          
            Log::info("EXISTE ZPL");
            $zplCode = base64_decode($etiqueta);
          
            $zplCode = $this->agregarQR($zplCode, $noGuia);
            $zplCode = $this->modificarDireccion($zplCode, $destino);
            $zplCode = $this->modificarNombre($zplCode,$destino,$origen);
            $zplResult = $ZPL->convertirZPL($zplCode,"pdf", $noGuia);

            return $zplResult;
        }

        $username = $this->configuracion->get('username');
        $password = $this->configuracion->get('password');
        $accessLicenseNumber = $this->configuracion->get('accessLicenseNumber');
        
        try{
            $options=[
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers'  => [
                    'Content-Type'=>'application/json',
                    'Username' => $username,
                    'Password' => $password,
                    'AccessLicenseNumber' => $accessLicenseNumber
                ],
                "json" => [
                    "LabelRecoveryRequest"=>[
                        "LabelSpecification"=>[
                            "HTTPUserAgent"=>"",
                            "LabelImageFormat"=>[
                                "Code"=>"ZPL"
                            ],
                            "LabelStockSize"=>[
                                "Height"=>"8",
                                "Width"=>"4"
                            ]
                        ],
                        "Translate"=>[
                            "LanguageCode"=>"spa",
                            "DialectCode"=>"US",
                            "Code"=>"01"
                        ],
                        "TrackingNumber"=>$noGuia
                    ]
                ],
            ];

            Log::info("GUIA ZPL:"); Log::info($noGuia);

            $client = new Client();
            $response = $client->request('POST', $this->endpointLabel,$options)->getBody()->getContents();
            // Log::info("RESPONSE ZPL:"); Log::info($response);

            if(isset($response->LabelRecoveryResponse->LabelResults->LabelImage->GraphicImage)){
                $zplCodeB64 = $response->LabelRecoveryResponse->LabelResults->LabelImage->GraphicImage;
                $zplCode = base64_decode($zplCodeB64);
                $zplCode = $this->agregarQR($zplCode, $noGuia);
            }else{

                Log::info("No hay ZPL:");

                throw new \Exception("No hay ZPL UPS",500);
            }
            
            }catch (\Exception $exception){
                Log::info("ERROR peticion ZPL:");
                Log::info($exception->getMessage());

                throw new \Exception($exception->getMessage(),$exception->getCode());
            }
        
        $zplResult = $ZPL->convertirZPL($zplCode,"pdf", $noGuia);

        return $zplResult;

    }

    public function changeImagen($zpl)
    {
        Log::info("Entra en changeImagen");
        $usuario = Auth::user()->id;
        Log::info("ID Usuario:". $usuario);
        $logo='';
        $file='';

        switch ($usuario) {
                case 9: //Sears (9 prod, 5 dev)
                    $logo = '
                    ^FO290,100^GFA,8190,8190,63,,::::::::::S01IFC,Q01MFC,P07OFCjO07JF,O03QFjM03MFE,O0RFCM03XFCO03FFE0IF8P07SFV03OFC,N03SFM07XF8O03FFC1IF8P07UFS01QF,N0TFCL07XF8O07FFC1IFCP0WF8Q0RFE,M03TFEL07XF8O07FF83IFCP0WFEP01SF8,M07UFL07XF8O0IF83IFCP0XF8O07SFC,M0VF8K0YFO01IF07IFCP0XFCN01UF,L03LFC003LFCK0YFO01FFE0JFCP0YFN03UF8,L07KFK01KFEK0YFO03FFE0JFCO01YF8M0VFC,L0KFM07KFJ01YFO07FFC1JFEO01YFEL01LF8001LFE,K01JFCI0FC001KFJ01YFO07FF81JFEO01gFL03KFK01LF,K03JF003JF007JF8I01YFO0IF83JFEO01gF8K0KF8L07KF,K07IFC01LF01JFCI01IFCgJ0IF07JFEO03gFCK0JFCN0KF8,K0JF00MFC0JFCI01IF8gI01IF07JFEO03IFQ03KFEJ03JF003JF807JF8,J01IFE07NF07IFCI03IF8gI03FFE0KFEO03IFR07KFJ07IFC03LF81JFC,J03IFC0OF83IFEI03IF87SFCN03FFC1KFEO03FFER01KFJ0JF80MFE0JFC,J03IF83OFC1IFEI03IF0TFCN07FFC1LFO07FFES07JF8I0IFE03NF03IFE,J07IF0PFE0IFEI03IF0TFCN07FF83LFO07FFE3QF01JF8001IFC1OFC1IFE,J0IFE1QF0JFI07IF0TF8N0IF83FFCIFO07FFE3QFE0JFC003IF83OFE1JF,J0IFC3QF87IFI07IF0TF8M01IF07FF8IFO07FFC3RF07IFC007IF07OFE0JF,I01IFC7IFC003JF83IFI07FFE1TF8M01FFE0IF8IFO0IFC3RFC3IFE007FFE1QF07IF,I01IF87IF8I0JFC3IFI0IFE1TF8M03FFE0IF8IF8N0IFC3RFE1IFE00IFC3JFE7KF87IF,I03IF0IFEJ03IFC3IFI0IFE1TFN03FFC1IF8IF8N0IFC7SF0IFE01IFC3IFC001JF83IF,I03IF0IFCJ01IFC1IF800IFE3TFN07FFC1IF87FF8N0IF87SF87FFE01IF87IF8I07IFC3IF8,I07FFE1IFCK0IFE1IF800IFE3TFN0IF83IF87FF8N0IF87SF87IF03IF8IFEJ03IFC1IF8,I07FFE1IF8K0IFE1IF800IFC3TFN0IF07IF87FF8M01IF87SFC3IF03IF0IFEJ01IFC1IF8,I07FFE3IF8K07FFE1IF801IFC3FFCX01IF07IF87FFCM01IF8TFC3IF03IF1IFCK0IFE1IF8,I0IFE3IF8K07FFE1IF801IFC7FFCX03FFE0JF87FFCM01IF8IFCK0KFE1IF07FFE1IFCK07FFE1IF8,I0IFC3IF8K07FFE1IF801IF87FFCX03FFE0JFC7FFCM01IF0IFCK03JFE1IF07FFE1IF8K07FFE0IF8,I0IFC3IF8K07FFE0IF801IF87FFCX07FFC1JFC3FFCM03IF1IF8L0JFE1IF07FFE3IF8K07FFE0IF8,I0IFC3IFCK03IF0IF803IF87FFCX07FF83JFC3FFCM03IF1IF8L03JF1IF07FFE3IFCK03FFE0IF8,I0IFC3IFEK03IF0IF803IF87FF8X0IF83JFC3FFCM03IF1IF8L03JF1IF07FFC3IFCK03FFE0IFC,I0IFC3JFU03IF0IF8W01IF07JFC3FFCM03FFE1IF8L01JF1IF0IFC3IFCK03FFE0IFC,I0IFC3JF8T03IF0IF8W01FFE0KFE3FFEM07FFE1IFM01JF1IF0IFC3IFEP0IF8,I0IFC3JFET07IF0IFX03FFE0KFE3FFEM07FFE3IFN0JF1IF0IFC3JF,I0IFE1KF8S07IF0IFX03FFC1KFE1FFEM07FFC3IFN0JF1IF0IFC3JFC,I0IFE1LFS07IF1IFX07FFC1KFE1FFEM07FFC3IFN0IFE1IF0IFE1JFE,I0IFE0LFER0IFE1FFEX0IF83KFE1FFEM0IFC3IFN0IFE3IF07FFE1KF8,I07FFE07LFEQ0IFE1FFEX0IF07FFCIF1FFEM0IFC7FFEM01IFE3FFE07FFE1LF8,I07IF07MFEP0IFE3SFCL01IF07FFCIF1IFM0IFC7FFEM01IFE3FFE07FFE0MF,I07IF83NFCO0IFE3SFCL01FFE0IF8IF0IFM0IF87FFEM03IFE3FFE07IF07LFE,I07IF80OFCN0IFC3SF8L03FFE0IF0IF0IFM0IF87FFEM07IFC3FFE07IF03MFE,I03IFC07OF8L01IFC3SF8L07FFC1IF0IF0IFL01IF8IFEM0JFC7FFC07IF81NFE,I03IFE01PFL01IFC3SF8L07FF83FFE0IF0IFL01IF8IFEL01JFC7FFC03IFC0OFC,I03JF007OFCK01IFC7SF8L0IF83FFC07FF0IF8K01IF8IFCL03JF87FF803IFE07OF8,I01JFC01PFK01IF87SF8K01IF07FFC07FF87FF8K01IF0IFCL0KF8IF803JF01PF,I01JFE007OF8J03IF87SFL01IF07FF807FF87FF8K03IF1UF0IF001JF807OFE,J0KF8007NFEJ03IF87SFL03FFE0IF007FF87FF8K03IF1TFE1IF001JFC01PF,J07JFEI0OFJ03IF87SFL03FFC1IF007FF87FF8K03FFE1TFC3IFI0KF003OFC,J07KFC001NF8I03IF0TFL07FFC1FFE003FF87FF8K03FFE1TF83FFEI0KFC00OFE,J03LFI01MFCI07IF0IF8V0IF83FFE003FF87FFCK07FFE1TF07FFCI07KFI0OF8,J01LFEI01LFEI07IF0IFW0IF83FFC003FFC7FFCK07FFE3SFE0IF8I03KFCI0NFC,K0MFEI03LFI07IF1IFV01IF07FF8003FFC3FFCK07FFC3SFC1IFJ03LF8001MFE,K07MFEI07KF8007IF1IFV01IF0IF8003FFC3FFCK07FFC3SF83IFJ01MF8001MF,K01NFEI0KF800IFE1SFCK03FFE0IFI01FFC3FFCK07FFC3SF07FFCK0NFI03LF,L07NFE003JFC00IFE1SFCK07FFC1FFEI01FFC3FFCK0IFC7RFC0IF8K03MFEI03KF8,L03OFE00JFC00IFE3SFCK07FFC3FFEI01FFE3FFCK0IFC7RF01IFL01OFI0KFC,M07OFC07IFE00IFC3SF8K0IF83FFCI01FFE3FFEK0IF87QF807FFEM07OF003JFC,N0OFE01IFE01IFC3SF8K0IF07FFCI01FFE1FFEK0IF87FFEP0IF8M01OFE00JFE,N01OF81IFE01IFC3SF8J01IF07FF8I01FFE1FFEJ01IF8IFEO03IFO07OFC03IFE,O01NFE0IFE01IFC3SF8J03FFE0IF8I01FFE1FFEJ01IF8IFCO0IFEP0PF01IFE,Q0NF07FFE01IF87SFK03FFC1PFE1FFEJ01IF0IFCN01IFCP01OFC0JF,R0MF87IF03IF87SFK07FFC1QF0IFJ03IF0QFC07FFCR0NFE07IF,3FFE1IFJ01LF83IF03IF87RFEK0IF83QF0IFJ03IF1RF83IFS0NF07IF,3FFE1IFK03KFC3IF03IF87FF8U0IF83QF0IFJ03IF1RFC1IFT0MF83IF,3FFE1IFL0KFC3IF03IF8IFU01IF07QF0IFJ03FFE1RFE0IF803FFE1IFK0LFC3IF,7FFE1IFL03JFC3IF07IF0IFU03FFE0RF0IFJ03FFE1SF0IFC03FFE1IFK01KFC3IF,7FFE1IFL01JFC3IF07IF0IFU03FFE0RF0IF8I07FFE1SF07FFC03FFE1IFL0KFE1IF,7FFE1IFL01JFC3IF07IF1IFU07FFC1RF87FF8I07FFE3SF87FFE03FFE1IFL03JFE1IF,7FFE1IFM0JFC3IF07IF1FFEU07FFC1RF87FF8I07FFC3SFC7FFE03FFE1IFL01JFE1IF,7FFE1IF8L07IFC3IF0IFE1FFEU0IF83RF87FF8I07FFC3SFC7IF03FFE1IF8L0JFE1IF,7FFE1IF8L07IFC3IF0IFE1FFET01IF87FFCP07FF8I07FFC3SFC7IF03FFE1IF8L0JFE1IF,3FFE1IF8L07IFC3IF0IFE1FFET01IF07FF8P07FF8I0IFC7IFK03JFC7IF03FFE1IF8L07IFE3IF,3FFE1IFCL0JFC3FFE0IFE3FFET03FFE0IF8P07FF8I0IFC7FFEL07IFC7IF03FFE1IF8L07IFC3IF,3IF1IFCL0JF87FFE1IFC3FFET03FFE1IFQ07FFCI0IF87FFEL03IFC7IF03IF0IF8L07IFC3IF,3IF0IFEK01JF87FFE1IFC3FFCT07FFC1SF83FFC001IF8IFEL03IFC7IF03IF0IFCL07IFC3IF,3IF0IFEK01JF8IFE1IFC3FFCT0IFC3SFC3FFC001IF8IFCL01IFC7IF03IF0IFCL0JFC7IF,3IF0JFK03JF0IFE1IFC3FFCT0IF83SFC3FFC001IF8IFCL01IFC7IF03IF0IFEK01JF87FFE,3IF07IF8J07IFE0IFC3IF87SFE001IF07SFC3FFC001IF0IFCL01IFC7IF03IF0JFK01JF87FFE,1IF07IFCI01JFE1IFC3IF87SFC001IF0TFC3FFC003IF0IF8L01IF87IF01IF0JFK03JF0IFE,1IF87JFI07JFC1IF83IF87SFC003FFE0TFE1FFE003IF1IF8L01IF87IF01IF87IF8J07JF0IFC,1IF83KFCLF83IF83IF8TFC007FFE1TFE1FFE003IF1IF8L01IF87IF01IF87IFEI01JFE0IFC,0IF83RF07IF07IF8TFC007FFC1TFE1FFE003FFE1IF8L01IF87IF01IF87JF8007JFC1IF8,0IFC1QFE0JF07IF0TFC00IF83TFE1FFE003FFE1IFM03IF87IF00IFC3KF07KF83IF8,07FFC1QFC1IFE07IF0TF801IF87TFE1FFE007FFE3IFM03IF87IF00IFC3RF07IF,07FFE0QF03IFC07IF1TF801IF07TFE0IF007FFE3IFM03IF87IF007FFC1QFE07IF,03IF07OFE07IFC07FFE1TF803FFE0IF8O0FFE0IF007FFC3IFM03IF8IFE007FFE0QFC0IFE,01IF81OF80JF80IFE1TF803FFE0IFP0IF0IF007FFC3IFM03IF0IFE003IF0QF81IFC,01IF80OF03JF00IFEW07FFC1FFEP0IF0IF00IFC7FFEM03IF0IFE003IF07OFE07IFC,00IFE03MF807IFE00IFEW0IFC3FFEP0IF0IF00IFC7FFEM03IF0IFE001IF81OF80JF8,007IF007KFC01JFC01IFEW0IF83FFCP0IF0IF00IFC7FFEM03IF0IFCI0IFC0NFE01JF,003IF8007IFE007JF801XFE01IF07FFCP0IF07FF80IF87FFCM03IF0IFCI0IFE03MF807IFE,001IFEJ0F8001KF801XFE01IF07FF8P07FF87FF81IF8IFCM03IF0IFCI07IF007KFE00JFE,I0JFCM07JFE001XFE03FFE0IFQ07FF87FF81IF8IFCM03IF8IFCI03IFC003IFC003JFC,I07JF8K07KFC003XFE07FFE1IFQ07FF87FF81IF8IFCM03IF8IF8I01JFO0KF8,I03LF001MF8003XFE07FFC3FFEQ07FF87FF81IF0IF8M03IF8IF8J0JFCM07JFE,I01UFEI03XFC0IF83FFEQ07FF87FF83IF0IF8M03IF8IF8J07JFCK03KFC,J0UF8I03XFC0IF87FFCQ07FFC3FF83IF1IF8M03IF8IF8J03KFEI07LF8,J07TFJ07XFC1IF07FF8Q07FFC3FFC3IF1IF8M03IF8IF8J01UFE,J01SF8J07XFC3IF0IF8Q07FFC3FFC3FFE1IFN03IF8IF8K0UF8,K07QFCK07XFC3FFE0IFR03FFC3FFC3FFE1IFN03IF87FFCK03SFE,K01QFL07XFC7FFC1FFER03FFC3FFC7FFE3IFN01IFC7FFCK01SF8,L07OF8jK07QFC,M07MF8jM0QF,O0JFEjO01OF,kJ01MF8,kL0KF,,::::::::^FS
                    ';
                    // $logo = ' Se cambia logo t1envios por solo de compañia
                    // ^FO380,64^GFA,8700,8700,50,,::::::::::I0WF8,001WFC,:001WF8,001WF81FFC,:001WF01FFC,001WF03FFC,001VFE03FFChK01C,001VFC07FFChK0F8,001VF807FFChJ03F8,001VF00IFChI01F7,001UFE01IFChI07EF,001UFC01IFChH03F9F,001UF003IFChG01FF3E,001TFI07IFChG01FE7E,O07JFL0JFChH0F8FC,O03IFEK01JFChH073FC,O07IFEK07JFChH027F8,O07IFEJ01KFChI0FF8,O07IFEI01LFChI07F8,O03IFEI0MFChI03F,O07IFEI0MFChI0DF,O07IFEI0MFChI0CE,O07IFEI0MFChI086,O07IFEI0MFC,O03IFEI0MFC,O07IFEI0MFC,O07IFEI0MFCW08P02S04,O07IFEI0MFCK03IFL01FC07FFEK07F8M0FF00FEL03IFO0IFC,O07IFEI0MFCK0JFEK01FC1JF8J03F8M0FF00FEK01JFEM07JF8,O07IFEI0MFCJ07KF8J01FC7JFEJ03FCL01FE00FEK07KF8K01KFE,O07IFEI0MFCJ0LFEJ01FDLF8I01FCL01FE00FEJ01LFEK07LF8,O07IFEL0JFCI03MFJ01NFCI01FEL01FC00FEJ03MFK0MFC,O07IFEL0JFCI07MFCI01NFEJ0FEL03FC00FEJ07MF8I01MFE,O07IFEL0JFCI0IF807FFEI01JF00IFJ0FFL03F800FEJ0IF807FFCI03FFE01IF,O07IFEL0JFC001FFCI0IFI01IFC003FF8I07FL07F800FEI01FFCI0FFEI03FFI03FF8,O07IFEL0JFC003FFJ03FF8001IFJ0FFCI07F8K07F800FEI03FFJ03FFI07FCJ0FF8,O07IFEL0JFC007FEK0FF8001FFEJ07FEI03F8K0FFI0FEI07FCJ01FF8007F8J03F8,O07IFEL0JFC007FCK07FC001FF8J01FEI03FCK0FFI0FEI0FF8K07FC00FFK03FC,O07IFEL0JFC00FF8K03FE001FF8J01FEI03FCJ01FEI0FEI0FFL03FC00FEK01FC,O07IFEL0JFC01FFL01FE001FFL0FFI01FEJ01FEI0FE001FEL01FE00FEK01FC,O07IFEL0JFC01FEM0FF001FEL07FI01FEJ03FCI0FE001FEL01FE00FFK01FC,O07IFEL0JFC01FCM07F001FEL07F8I0FFJ03FCI0FE003FCM0FF00FF8,O07IFEL0JFC03FCM07F001FEL03F8I0FFJ07F8I0FE003F8M0FF007FC,O07IFEL0JFC03F8M03F801FCL03F8I07F8I07F8I0FE003F8M07F007FF8,O07IFEL0JFC03F8M03F801FCL03F8I07F8I0FFJ0FE007F8M07F803IF,O07IFEL0JFC03F8M03F801FCL03F8I03FCI0FFJ0FE007FN03F801IFE,O07IFEL0JFC07QF801FCL03F8I03FC001FEJ0FE007FN03F800JFE,O07IFEL0JFC07QFC01FCL03F8I01FE001FEJ0FE007FN03F8003JFC,O07IFEL0JFC07QFC01FCL03F8I01FE003FCJ0FE007FN03F8001KF8,O07IFEL0JFC07QFC01FCL03F8J0FF003FCJ0FE007FN03F8I03KF,O07IFEL0JFC07QFC01FCL03F8J0FF007F8J0FE007FN03F8J0KFC,O07IFEL0JFC07QFC01FCL03F8J07F007F8J0FE007FN03F8K0JFE,O07IFEL0JFC07FCN02801FCL03F8J07F807FK0FE007FN03F8K01JF8,O07IFEL0JFC03F8Q01FCL03F8J03F80FFK0FE007F8M07F8L01IFC,O07IFEL0JFC03F8Q01FCL03F8J03FC0FEK0FE007F8M07FN03FFC,O07IFEL0JFC03FCQ01FCL03F8J01FC1FEK0FE003F8M07FO07FE,O07IFEL0JFC03FCQ01FCL03F8J01FE1FEK0FE003FCM0FFO01FE,O07IFEL0JFC01FEQ01FCL03F8K0FE3FCK0FE003FCM0FE01N0FF,O07IFEL0JFC01FEM04I01FCL03F8K0FF3FCK0FE001FEL01FE03F8L07F,O07IFEL0JFC00FFM0FI01FCL03F8K07F7F8K0FE001FFL03FE03FCL07F,O07IFEL0JFC00FF8K01FC001FCL03F8K07IF8K0FEI0FF8K07FC01FCL0FF,O07IFEL0JFC007FCK03FE001FCL03F8K07IFL0FEI07FCK0FF801FEL0FE,O07IFEL0JFC003FFK0FFC001FCL03F8K03IFL0FEI07FEJ01FF801FFK03FE,O07IFEL0JFC001FF8I01FFC001FCL03F8K03FFEL0FEI03FF8I07FFI0FFCJ07FE,O07IFEL0JFC001IFI0IF8001FCL03F8K01FFEL0FEI01FFE003FFEI0IFI03FFC,O07IFEL0JFCI07NFI01FCL03F8K01FFCL0FEJ0NFCI07NF8,O07IFEL0JFCI03MFCI01FCL03F8L0FFCL0FEJ07MF8I03NF,O07IFEL0JFEI01MF8I01FCL03F8L0FF8L0FEJ01LFEJ01MFE,O07IFEL0JFCJ07KFEJ01FCL03F8L07F8L0FEK0LFCK07LF8,O07IFEL0JFCJ01KFCJ01FCL03F8L07FM0FEK03KFL01KFE,O07IFEL0JFCK07IFEK01FCL03F8L03FM0FEL07IF8M03JF8,O07IFEL0JFCL07FEL01FCL03F8L03EM0FEM0FF8O03FF,O03IFEL0JFC,:O03IFEL0JF,O07IFEL0IF8,O07IFEL0FFC,O07IFEL0FE,O03IFEL0F8,O07IFE,O03IFE,P0IFE,P01FFE,Q03FE,R0FE,R01E,S06,,::::::::::gX03FE01IFE0066003IFI07F8,gX0F8F01IFE00EF003IFC01FFE,gW0380381CK0DF003001E079C7,gW073F9C39FFC01DF0073E470E7F38,gW06F7CC3BFFC01BF0077FF30CFF98,gW0CC1EE3B8I033B006IFB9983DC,gW0DC0EE33J077B806C0399B81DC,gW0DCI036J067B806C0199B8,gW0DEI076J06FB80EC01B9BC,gW0EFE0076J0CDD80CC03B9DF8,gW0F3FC077FF01D8D80DC0331E7F8,gW07E3E06IF0198D80DC0E60F07C,gW03F8F06EI03B0D81DFFCC07E1E,gX0FE78EIF0320DC1DC01803F8F,gX01FB8CFFE0660EC19C018007E7,gY07B8DCI067FEC1BFFDCI0F7,gV07703D8D8I0CFFCC1BCFDC6C0738,gV07701D9D8I0DC00C1B00FC7E03B,gV07703B9D80019FFEE3B00EC6E073,gV03383399C001BFFE63300DC6F077,gV033FF319FFE3300663700DC77FE6,gV038FCF180047600663700DC7BF8E,gV01E01E3IFC6600363601DC3C13C,gW0IF83IFCEC00367E01FC1F7F,gW01FCY07FE,,:::::::::::::::::::::::::::::::::::::::::::::::^FS
                    // ';
                    $file = substr_replace($zpl, $logo,123, 2980);
                    break;

                case 11://Sanborns (11 prod, 7 dev)
                    $logo = '
                    ^FO290,100^GFA,6363,6363,63,,::::::V07JFE,T03MF8,S0OFC,Q01PFE,P03QFE,O01RFE,O0SFE,N07SFE,M03TFE,M0UFC,L03MFE003JFC,K01NF8003JF8,K03MFCI0KFh0FE,K0NFC007JFEgY07FE,J03NFE1LF8gY0FFE,J0WFgY03FFE,I01LFC7NFCgY07FFE,I07KFE01NFgY01IFE,I0LF8003LF8gY03IFE,003KFCJ01JF8h07IFC,007KF8L018hH0JFC,00KFCX01FF8gO0JFC,01KF8W01JF8gN03IF8,03JFEX0LFgN01IF8,07JF8W03FFC3FFCgM01IF,0KFX0FF8001FFgM01IF,1JFCW01FCJ03F8gL01IF,1JF8W07FL0FEgL03FFE,3JFX0FCL03FgL03FFE,3JFW01F8L01F8gK07FFC,3JFW03FN07CgK07FFC,7JFW07EN03EgK0IF8,7JF8V0FCN01EgK0IF8,7JFCU01F8O0FgJ01IF8,7QFCN01FP078gI01IF,7SFM03EP078gI03IF,7TFL07CP03CgI03FFE,7TFEK07CP01EgI07FFE,7UFCJ078P01EgI0IFEhT0IF8,3VFJ0F8Q0EgI0IFCP01ChG07IFC,3VFCI0FR0FgH01IFC03FL01FF8h0JF8,1WF001FR0FM0CM0CL01IF81FF8K07FFEL018gQ03JF8,0WF001FR078K03FCK0FF8K03IF83FFCK0IFE01EI03KF8M03EL03FO0KF,07VF801EM01FI078K0IFJ03IFK03IF8IFCJ01JF0FFC003KFEM0FFCK0FFEM03JFE,01VFC01EL07FFE0078J01IF8I07IF8J07IF9IFEJ03MFC003LFL03FFEJ01IFM07JFE,001UFC03EK0KF0038J07IFCI0JFCJ07MFEJ07MFC003LF8K0JF8I03IF8L0KFC,K03RFC03EJ07JFE0038J07IFC001JFEJ0NFEJ0NFC003LF8J01JFCI07IFCK03KFC,N0PFE03CI01JFEI03CJ0JFE003JFEJ0NFEI01NFC003LF8J03JFE001JFCK07KF8,P07MFE03CI07IFCJ03CI01JFE007KFI01NFEI07NFC003LFCJ0KFE003JFEK0LF8,Q03LFE03CI0IFEK03CI03KF01LFI01NFEI0OFC007FCJFCI01KFE007JFEJ01LF8,R01KFE03C001IF800FE03CI03KF03LFI03IFCKF003JF9JFC00FF87IFCI03LF00LFJ03FF3IF8,S03JFE03C003FFE001FF03CI07KF07LFI03IF8KF80KF1JF801FF03IFCI07LF01LFJ07FC3IF8,T0JFC03C007FFC003FF03CI0LF0MFI07IF8QFE1JF803FE07IFCI0MF07LFJ0FF83IF,T07IFC03C00IF800IF03C001LFBMFI07IF07PFC1JF807FE0JF8001MF0MFI03FF03IF,T03IFC03E01IF001IF03C001TFI0JF03PFC1JF81FFC0JF8003MF1LFEI07FE03IF,T03IF803E01FFE003IF038003TFI0JF01PF80JFC7FF81JFI07FE3JF3LFEI0FFC07IF,T01IF801E03FFC007IF078007NFE7IFE001IFE00PF807MF03IFEI0FFC3QFE001FFC07IF,T03IF001E03FFC01IFE07800OFC7IFE001IFE007OF007LFE07IFE001FF83LFEJFE003FF80JF,T03IF001F03FF803IFE07801FEMF87IFC001IFC007OF007LFC07IFC003FF03LFC7IFC007FF81JF,T03FFE001F03FF00JFC0F803FC7KFE07IFC001IFC007NFE007LF80JF8007FE03LF87IFC00UF8,T07FFCI0F03FF01JFC0F807F87KFC07IF8003IFC00JF81IFE007KFE01JF800FFC07LF07IF801UFC,T0IF8I0F83FF07JF81F00FF07KF80JF8003IFC00IFI0IFE007KFC01JF001FF807KFE07IF803UFC,S01IFJ0781FF1FF1FF81F01FE07KF00JFI03IFC01FFC001IFE007JFE001IFE003FF00LFC07IF007UF8,S07FFEJ07C1FF7FC0FF83E03F80KFE00JFI03IFC03FF8001IFC007JFI03IFE007FE00LF80JF00VF,R03IFCJ07E0JF80FF87C07F00KFC01IFEI03IFC03FFI01IFC00FFCK03IFC00FFC01LF00JF01VF,7P01JF8J03E07FFE007FCFC0FE00KF801IFEI03IF807FFI01IFC01FF8K03IFC01FF801KFE00IFE03UFE,FFN03KFK01F03FFI07IF01FC01KF001IFCI03IF807FEI01IFC01FFL03IF803FF801KFE00IFE07FE3RFC,IFL03KFCK01F807J03FFE03F801KF001IFCI03IF80FFCI01IFC03FEL03IF807FF001KFC01IFE0FF81RF8,UF8L0FCM0FF80FE001JFE001IFCI03IF81FF8I01IFC07FEL03IF80FFE001KF801IFC1FF00LF8,TFEM07EP03FC001JFC001IF8I01IF83FFK0IFC0FFCL03IF01FFC001KFI0IFE7FE007KF,7SF8M03FP0FF8003JFC001IF80F01IF87FEK0IFC1FF8L03IF07FF8001JFEI0LFC007JFE,3RFEN01FCN03FEI01JF8I0IF81F00LF8K0IFE7FFM01IF8IFI01JFCI07KFI03JFC,1RFP0FEM01FFCI01JFJ07FFC7F00LFL07KFCM01LFCJ0JF8I03JFEI01JF8,07PF8P07F8L0IFK0IFEJ07KF007JFEL03KF8N0LF8J07IFK0JF8J0JF,00OF8Q03FFK07FFCK07FFCJ01JFE001JF8L01JFEO07KFK03FFCL0FFEK03FFE,I01KFET0IFC07JFL03FF8K0JFCI0IFCN07IFP01JFCL07FV0FF8,gJ07NF8M01CM0IFW01ER03FFE,gJ01MFCX03,gK03KFC,gL07IFC,,:::::::::::::^FS
                    ';
                    //$logo = ' Se cambia logo t1envios por solo de compañia
                    //^FO300,15^GFA,18900,18900,63,,:::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::J05gF,I01gGFC,I03gGFC,:I07gGF8,I03gGF807FFC,I03gGF007FFC,I03gGF00IFC,I03gGF00IFE,I03gFE01IFE,I03gFC01IFEhW07,I03gFC03IFEhV03E,I03gF803IFEhU01FE,I03gF007IFEhU07FE,I03YFE007IFEhT03FBC,I03YFC00JFEhT0FE7C,I03YFI0JFEhS07FCF8,I07XFC003JFEhR01FF1F8,I03XFI03JFEhR0FFE7F,I03WFJ07JFEhR0FFCFF,Q03KFCM0KFEhR07F1FF,Q01KF8L03KFEhR01E3FE,Q01KF8L07KFEhS0C7FE,Q01KF8K01LFEhS08FFC,Q01KF8K0MFEhS01FFC,Q01KF8J03MFEhS01FF8,Q01KF8I07NFEhT0FF8,Q01KF8I0OFEhS033F8,Q01KF8I0OFEhS039F,Q01KF8I0OFEhS038F,Q01KF8I0OFEhS0106,Q01KF8I0OFE,::::Q01KF8I0OFEM07IF8M01FF003IF8L07FEO01FF003FEN07IFQ01IFE,Q01KF8I0OFEL03KFM01FF01KFL03FEO03FF003FEM03KFP0KFE,Q01KF8I0OFEL0LFCL01FF07KFCK03FFO07FE003FEL01LFCN03LF8,Q01KF8I0OFEK03MFL01FF1MFK01FFO07FE003FEL07MFN0MFE,Q01KF8I07NFEK0NFCK01FF7MF8J01FF8N07FC003FEL0NFCL03NF,Q01KF8M0KFEJ01OFK01PFCK0FF8N0FFC003FEK03NFEL07NFC,Q01KF8M0KFEJ07OF8J01QFK0FFCN0FF8003FEK07OF8K0OFE,Q01KF8M0KFEJ0PFCJ01QF8J07FCM01FF8003FEK0PFCJ01PF,Q01KF8M0KFEI01IFE001IFEJ01KF001IFCJ07FEM01FFI03FEJ01IFE001IFEJ03IF8003IF,Q01KF8M0KFEI03IFJ03IFJ01JF8I07FFCJ03FEM03FFI03FEJ03IFJ03IFJ07FFCJ0IF8,Q01KF8M0KFEI07FFCK0IF8I01IFEJ01FFEJ03FFM03FEI03FEJ07FFCK0IF8I07FFK03FFC,Q01KF8M0KFEI0IFL03FFCI01IFCK0IFJ01FFM07FEI03FEJ0IFL07FF8I0FFEL0FFC,Q01KF8M0KFEI0FFEL01FFCI01IF8K03FF8I01FF8L07FEI03FEI01FFEL01FFCI0FFCL07FC,Q01KF8M0KFE001FFCM0FFEI01IFL01FF8J0FF8L0FFCI03FEI01FFCM0FFEI0FF8L07FC,Q01KF8M0KFE003FF8M07FFI01FFEL01FF8J0FFCL0FFCI03FEI03FF8M07FEI0FF8L03FE,Q01KF8M0KFE003FFN03FFI01FFCM0FFCJ07FCK01FF8I03FEI03FFN03FFI0FF8L03FE,Q01KF8M0KFE007FEN01FF8001FFCM07FCJ07FEK01FF8I03FEI07FEN03FFI0FF8L03FE,Q01KF8M0KFE007FEO0FF8001FF8M07FEJ07FEK03FFJ03FEI07FCN01FF800FFC,Q01KF8M0KFE007FCO0FFC001FF8M03FEJ03FFK03FFJ03FEI0FFCO0FF800FFE,Q01KF8M0KFE00FFCO07FC001FFN03FEJ03FFK07FEJ03FEI0FF8O0FFC00IF8,Q01KF8M0KFE00FF8O07FC001FFN03FEJ01FF8J07FEJ03FEI0FF8O0FFC007FFE,Q01KF8M0KFE00FF8O03FC001FFN03FEJ01FF8J0FFCJ03FE001FFP07FC003IFC,Q01KF8M0KFE00FF8O03FE001FFN01FEK0FFCJ0FFCJ03FE001FFP07FC003JF8,Q01KF8M0KFE01FF8O07FE001FFN01FEK0FFCJ0FF8J03FE001FFP07FC001KF8,Q01KF8M0KFE01TFE001FFN01FEK07FCI01FF8J03FE001FFP03FEI07KF,Q01KF8M0KFE01TFE001FFN01FEK07FEI01FFK03FE001FFP03FEI03LF,Q01KF8M0KFE01TFE001FFN01FEK03FFI03FFK03FE001FFP03FEJ0LFE,Q01KF8M0KFE01TFE001FFN01FEK03FFI03FEK03FE001FFP03FEJ03LFC,Q01KF8M0KFE01TFE001FFN01FEK01FFI07FEK03FE001FFP03FEK07LF,Q01KF8M0KFE01TFE001FFN01FEK01FF8007FCK03FE001FFP03FEL0LFC,Q01KF8M0KFE01TFE001FFN01FEL0FF800FFCK03FE001FFP03FEL01KFE,Q01KF8M0KFE01TFE001FFN01FEL0FFC00FF8K03FE001FFP03FEM03KF8,Q01KF8M0KFE01FF8T01FFN01FEL07FC01FF8K03FE001FFP07FCN03JFC,Q01KF8M0KFE00FF8T01FFN01FEL07FE01FF8K03FE001FFP07FCO07IFE,Q01KF8M0KFE00FF8T01FFN01FEL03FE03FFL03FEI0FF8O07FCP0IFE,Q01KF8M0KFE00FFCT01FFN01FEL03FF03FFL03FEI0FF8O0FFCP01IF,Q01KF8M0KFE00FFCT01FFN01FEL01FF07FEL03FEI0FFCO0FF8Q07FF,Q01KF8M0KFE007FCT01FFN01FEL01FF87FEL03FEI0FFCN01FF8Q03FF8,Q01KF8M0KFE007FET01FFN01FEM0FF8FFCL03FEI07FEN01FF803FCM01FF8,Q01KF8M0KFE003FFO0EJ01FFN01FEM0FFCFFCL03FEI07FFN03FF007FCN0FF8,Q01KF8M0KFE003FF8M01FJ01FFN01FEM07FDFF8L03FEI03FFN07FF007FCN0FF8,Q01KF8M0KFE001FFCM03FCI01FFN01FEM07JF8L03FEI03FF8M0FFE003FEN0FF8,Q01KF8M0KFE001FFEM07FEI01FFN01FEM03JFM03FEI01FFCL01FFC003FEM01FF8,Q01KF8M0KFEI0IFM0IFI01FFN01FEM03JFM03FEJ0IFL03FFC003FFM03FF,Q01KF8M0KFEI07FF8K03FFEI01FFN01FEM03IFEM03FEJ07FF8K0IF8001FFCL07FF,Q01KF8M0KFEI03FFEK0IFCI01FFN01FEM01IFEM03FEJ07FFEJ01IFI01IFK01FFE,Q01KF8M0KFEI01IFCI03IF8I01FFN01FEM01IFCM03FEJ03IF8I0IFEJ0IFCJ0IFE,Q01KF8M0KFEJ0JFC03JFJ01FFN01FEN0IFCM03FEJ01JF80JFCJ0JFE01JFC,Q01KF8M0KFEJ07OFEJ01FFN01FEN0IF8M03FEK0PF8J07PF8,Q01KF8M0KFEJ03OFCJ01FFN01FEN07FF8M03FEK03OFK01PF,Q01KF8M0KFEJ01OF8J01FFN01FEN07FFN03FEK01NFCL0OFE,Q01KF8M0KFCK07MFEK01FFN01FFN03FFN03FEL07MF8L07NF8,Q01KF8M0KFCK01MF8K01FFN01FFN03FEN03FEL03LFEM01MFE,Q01KF8M0KFCL07KFEL01FFN01FFN01FEN03FEM07KF8N03LF8,Q01KF8M0KFEM0KFM01FFN01FFN01FCN03FEM01JFCP0KFC,Q01KF8M0KFEN0FFEN01FEN01FEO0FCN03FEO0FF8R03FF,Q01KF8M0KFE,Q01KF8M0KFC,Q01KF8M0KF8,Q01KF8M0JFC,Q01KF8M0IFE,Q01KF8M0IF,Q01KF8M0FF8,Q01KF8M0FE,Q01KF8M0F,Q01KF8,:R03JF8,S07IF8,S01IF8,T03F8,U0F8,U018,,:::::::::::::::gP03IFC,gO07JFE,gM03MF,gL03NF8,gK03OF8,gJ01JF80JF8,gJ0KF007IF,gI03JFE003FFE,gH01KFE007FFC,gH07KFE00IF8gP078,gH0JF9FE01IFgP01FC,gG03IFC03KF8gP03FC,gG07IFI07IFEgQ0FFC,g01IFCJ07FgR01FF8,g03IFgY03FF8,g0IFCgY01FF8,Y01IF8gY01FF,Y03FFEh01FF,Y07FF8h01FE,Y0IFT01gL01FE,Y0IFT06K06g01FE,X01FFEhG01FC,X01FFEhG03FC,X01IFS02gM07F8,X01IF8R04gM07F8,X01IFCR0CgM0FF8,X01OFL018gM0FF,X01PF8J01gM01FF,X01QFJ01gM01FE,Y0QFEgQ03FEhG03FC,Y0RF8gP03FEN078gP01FFC,Y03QFC004gM07FE07CJ01FEgP07FFC,g07PFE004T01FJ01FK0FFE1FEJ07FF8J03IF8L0EK0FM0IF8,gH0PF004K0F8M07FCI07FCJ0KFEJ0KFC003IFEK03F8I03FCK03IF8,gK0MFM0FFN0FFE001FFEI01LFI01KFE007JFK0FFCI07FEK07IF,gL07KFL07FCM01IF003IFI01LFI03KFE007JF8I03IFI0IFK0JF,gM01JF8J01FFN03IF007IF8003LFI0LFE007EIF8I07IF003IFJ03JF,gN03IFK03FCN03IF80JF8003LF801FFC3FFE00F87FF8I0JF807IF8I07EFFE,gN01IFK0FFI04K07IF83JF8007FF1IFC07FF83FFC01F03FF8001JF80JF8I0F87FE,gO07FFJ01FE001FK0JFC7JF8007FE0NF81FFC03E03FF8007JF81JF8001F03FE,gO07FFJ01FC007FJ01PF800FFE07MF01FFE07E03FFI0KF83JF8003F03FE,gO03FE00403F800FFJ01PF800FFC03LFE00IF1FC07FF001F8IFCKFI07E07FE,gO03FE00403F803FFJ03LF8IFI0FFC03LFE00KF80FFE003F07NFI0FE07FE,gO03FCJ07F007FEJ07KFE0IFI0FFC01LFC00KF01FFC007E07JF8IF001FE0IF,gO07FCJ07E00FFEJ0F1JFC0FFE001FF801FFC3FFC00JFC01FF800FC07IFE0IF003FE3IF8,gO07F800207F00FFCI01C0JF80FFE001FF803FC01FFC00JF003FF801F807IFE07FE007NFC78,gO0FFI0303F001FC080380JF00FFC001FF803FI0FF800IFC003FF001F00JFC07FE00QFC,gN03FEI0103F800FEI0700IFE00FFC001FF807EI0FF801FEJ07FE003E00JF80FFC01QF8,gN0FFCI0181FC007EI0E01IFC01FF8001FF807CI0FF801F8J07FE007C01JF00FFC07QF8,gM07FFK0CK01E001C01IF801FF8001FF00F8I0FF803FK07FE00F801IFE00FFC0FC7NFE,X01CL03FFEK0EO03001IF001FF8001FF01FJ0FF803CK07FC03F001IFC00FFC1F01JFE,X01FEJ07IF8K07O06003IF001FFI01FF83EJ0FF8078K07FC07E001IF800FFE3E01JF,X01OFCL038M038003FFE001FFJ0FF87CJ07FC0FL07FC0FC001IFI07IF800IFE,Y0OFM01CM07I01FFCI0FF8I0FFEF8J07FC1EL03FE1F8I0FFEI03IFI03FFC,Y03MFO0EM08I01FF8I07F86007FFEK03IF8L01JFJ07FCJ0FFCI01FF8,g01KFP07R0FFJ03FFE003FF8L0FFEN0IFCJ03FK01FK07E,gV01EJ0CM08K07FCS0FO03FF,gW03E01FC,gX0IFE,gY07,,::::::::::::::::::::::::::::::::::::::::::::::::::::^FS
                    //';
                    $file = substr_replace($zpl, $logo,123, 2980);
                    break;

                default: //Claro (10 prod, 6 dev)
                    $logo = '
                    ^FO290,100^GFA,8568,8568,63,,::::::::::hS0FFE,::::::::hS0FFEP02,hS0FFEP07,hS0FFEP0F8,hS0FFEO01FC,hS0FFEO03FE,hS0FFEO07FF,hS0FFEO0IF8,hS0FFEN01IFC,hS0FFEN03IFE,hS0FFEN07IFC,hS0FFEN0JF8,hS0FFEM01JF,hS0FFEM03IFE,hS0FFEM07IFC,hS0FFEM0JF8,hS0FFEL01JF,hS0FFEL03IFE,hS0FFEL07IFC,hS0FFEL0JF8,hS0FFEK01JF,N01FF8hG0FFEK03IFE,M03JF8h0FFEK07IFC,L01LFM0IF8gO0FFEK0JF8,L07LFEL0IF8gO0FFEJ01JF,K01NFL0IF8gV03IFEgH03FFE,K07NFCK0IF8gV07IFCgH03FFE,K0OFEK0IF8gV0JF8gH03FFE,J01PF8J0IF8gU01JFgI03FFE,J07PFCJ0IF8gU03IFEgI03FFE,J0QFEJ0IF8gU01IFCgI03FFE,I01RFJ0IF8gV0IF8gI03FFE,I03RF8I0IF8gV07FFgJ03FFE,I07RFCI0IF8gV03FEgJ03FFE,I07RFEI0IF8gV01FCgJ03FFE,I0KFC003KFI0IF8gW0F8gJ03FFE,001JFEJ0KFI0IF8K01FFCY03FFCK07gK03FFE,001JF8J03JF800IF8J01JFCQ0FCJ01JF8J02gK03FFE,003JFK01JFC00IF8J0LFCO07FCJ0LFgP03FFE,007IFCL07IFC00IF8I03MFJ0IF81FFCI01LFCgO03FFE,007IF8L03IFE00IF8I07MF8I0IF83FFCI07MFgO03FFE,00JFM01IFE00IF8I0NFEI0IF8IFC001NF8gN03FFE,00IFEN0IFE00IF8001NFEI0IF9IFC003NFCgN03FFE,00IFEN07IF00IF8003OFI0IFBIFC007NFEY03FFCK03FFE03FEO0FFEQ03F8,01IFCN03IF00IF8007OF800MFC00PFX03JFCJ03FFE0IFCM0JFEK07FFC1IF,01IF8N03IF80IF8007OF800MFC01PF8W0LF8I03FFE1IFEL03KF8J07FFC3IFC,03IF8N01IF80IF800PFC00MFC03PFCV03LFEI03FFE7JFL07KFCJ07FFC7IFE,03IFU0IF800IFE003IFC00MFC03PFEV07MF8003FFE7JF8J01MFJ07FFCKF,03IFU0IF800IFCI0IFC00MFC07QFV0NFE003FFEKFCJ03MF8I07FFDKF8,03FFEU0IF801IF8I07FFC00LF800JFE003JFU01OF003NFEJ07MFCI07NF8,03FFEU0IF801IFJ07FFC00KFCI0JF8I0JF8T03NFE003OFJ07MFCI07NFC,03FFEU0IF801IFJ07FFC00JFEI01IFEJ07IF8T03NFC003OFJ0NFEI07NFE,03FFEU0IF801FFEJ07FFC00JFCI01IFCJ03IFC007MFJ07NF8003OF8001OFI07NFE,07FFEU0IF801FFEJ07FFC00JF8I03IF8J01IFC007MFJ07NFI03OF8001OFI07NFE,07FFEU0IF8O07FFC00JFJ03IFL0IFC007MFJ07MFEI03OF8001OFI07OF,07FFCU0IF8O0IFC00JFJ07IFL07FFE007MFJ07FF8003FEI03IFE07IF8003IFC07IF8007IF807IF,07FFCU0IF8N01IFC00IFEJ07FFEL03FFE007MFJ0IFJ07CI03IF801IFC003IF001IF8007IF003IF,07FFCU0IF8N0JFC00IFEJ07FFCL03FFE007MFJ0IFJ018I03IFI0IFC003FFEI0IF8007FFE001IF,07FFEU0IF8L03KFC00IFCJ07FFCL01IF007MFJ0IFO03FFEI07FFC007FFEI0IFC007FFE001IF,07FFEU0IF8J01MFC00IFCJ07FFCL01IF007MFJ0IFO03FFEI07FFC007FFCI07FFC007FFCI0IF8,03FFEU0IF8J0NFC00IFCJ07FF8L01IF007MFJ0IFEN03FFEI07FFC007FFCI07FFC007FFCI0IF8,03FFEU0IF8I07NFC00IFCJ0IF8L01IF007MFJ07JFCL03FFEI07FFC007FFCI07FFC007FFCI0IF8,03FFEU0IF8001OFC00IF8J0IF8L01IF007MFJ07LF8J03FFEI03FFC007FFCI07FFC007FFCI0IF8,03IFU0IF8003OFC00IF8J0IF8L01IFT07MF8I03FFEI03FFC007FFCI07FFC007FFCI0IF8,03IFU0IF8007LF7FFC00IF8J0IF8L01IFT03MFEI03FFEI03FFC007FFCI07FFC007FFCI0IF8,03IF8T0IF800LF87FFC00IF8J0IF8L01IFT03NFI03FFEI03FFC007FFCI07FFC007FFCI0IF8,01IF8N03IF80IF801KF807FFC00IF8J07FF8L01IFT01NF8003FFEI03FFC007FFCI07FFC007FFCI0IF8,01IF8N03IF00IF801JFI07FFC00IF8J07FF8L01IFU0NFC003FFEI03FFC007FFCI07FFC007FFCI0IF8,00IFCN07IF00IF803IF8I07FFC00IF8J07FFCL01IFU07MFC003FFEI03FFC007FFCI07FFC007FFCI0IF8,00IFEN07FFE00IF803IFJ07FFC00IF8J07FFCL03IFU03MFE003FFEI03FFC007FFCI07FFC007FFCI0IF8,00JFN0IFE00IF803FFEJ07FFC00IF8J07FFCL03FFEV03LFE003FFEI03FFC007FFCI07FFC007FFCI0IF8,007IFM01IFE00IF807FFEJ07FFC00IF8J07FFEL07FFEW03LF003FFEI03FFC007FFCI07FFC007FFCI0IF8,007IFCL03IFC00IF807FFCJ07FFC00IF8J03IFL07FFCY01JF003FFEI03FFC007FFCI07FFC007FFCI0IF8,003IFEL07IFC00IF807FFCJ07FFC00IF8J03IFL0IFCg01IF003FFEI03FFC007FFCI07FFC007FFCI0IF8,003JFK01JF800IF807FFCJ07FFC00IF8J01IF8J01IFCgG0IF003FFEI03FFC007FFCI07FFC007FFCI0IF8,001JFCJ03JFI0IF807FFCJ0IFC00IF8J01IFCJ03IF8gG07FF003FFEI03FFC007FFCI07FFC007FFCI0IF,I0KFI01KFI0IF807FFCJ0IFC00IF8J01JFJ0JF8U06K07FF003FFEI03FFC007FFEI0IF8007FFE001IF,I07JFE00KFEI0IF807FFCI01IFC00IF8K0JF8001JFV0F8J07FF003FFEI03FFC003IF001IF8007FFE001IF,I07RFCI0IF807FFEI03IFC00IF8K07JF00KFU01FEJ0IF003FFEI03FFC003IF803IF8007IF003IF,I03RF8I0IF807FFEI07IFC00IF8K07PFEU03FFE003IF003FFEI03FFC003JF1JF8007IFC0JF,I01RFJ0IF807IF001JFC00IF8K03PFCU03OF003FFEI03FFC001OFI07OF,J0QFEJ0IF803IFE0KFC00IF8K01PFCU07NFE003FFEI03FFC001OFI07NFE,J07PFCJ0IF803PFC00IF8L0PF8U0OFE003FFEI03FFCI0NFEI07NFE,J03PF8J0IF803PFC00IF8L0PFU01OFC003FFEI03FFCI0NFEI07NFC,K0PFK0IF801PFC00IF8L03NFEU03OFC003FFEI03FFCI07MFCI07NFC,K07NFCK0IF800PFC00IF8L01NF8U03OF8003FFEI03FFCI03MF8I07NF8,K01NF8K0IF8007OFC00IF8M0NFW0OFI03FFEI03FFCI01MFJ07FFDKF,L07LFEL0IF8003KFE7FFC00IF8M03LFCW07MFEI03FFEI03FFCJ0LFEJ07FFCKF,L01LF8L0IF8001KFC7FFC00IF8M01LF8W01MF8I03FFEI03FFCJ07KFCJ07FFCJFE,M03JFCM0IF8I07IFE07FFC00IF8N03JFCY03KFEJ03FFEI03FFCJ01KFK07FFC3IF8,N03FFCN0IF8I01IF007FFCU07IFgG07JF8J03FFEI03FFCK07IFCK07FFC1FFE,iY0FEgI01FM07FFC01E,kS07FFC,:::::::::::::::::,::::::^FS
                    ';
                    // $logo = ' Se cambia logo t1envios por solo de compañia
                    // ^FO300,15^GFA,13481,13481,61,,::::::::::M03XF,M0YF8,:L01YF8,L01YF801F,L01YF80FFC,L01YF81FFE,L01YF03FFE,L01XFE03FFE,L01XFE07FFE,L01XFC0IFEhO03,L01XFC0IFEhO0F8,L01XF81IFEhN07F8,L01XF01IFEhM01FF8,L01WFE03IFEhM0IF,L01WF807IFEhL07IF,L01VFE00JFEhL0IFE,M0VF801JFEhK01IFE,M03TFC003JFEhK01IFC,S01MFCI07JFEhL0IFC,T0KFCJ01KFEhL07FFC,T0KF8J03KFEhL07FF8,T0KF8I03LFEhL07FF8,T0KFI01MFEhL03FF8,T0KFI03MFEhL03FF,T0KFI03MFEhL03FE,T0KFI07MFEhL01FE,T0KFI07MFEhN0C,T0KFI07MFE,:::::T0KFI07MFEK01JFCL01E01JF8K03EN01FI038L03JF8N0KF,T0KFI03MFEK07KFL03F07JFCK07FN03FI07CL0KFEM03KFC,T0KFI01MFEJ01LFCK03NFK03FN07FI07CK03LF8L0LFE,T0KFL07JFEJ03MFK03NFCJ03F8M07FI07CK07LFCK01MF8,T0KFL03JFEJ0NF8J03NFEJ01FCM0FFI07CJ01NFK03MFC,T0KFL03JFEI01FFE003FFCJ03IFE007FFJ01FCM0FEI07CJ03FFC00IF8J07FF001FFE,T0KFL01JFEI03FF8I07FEJ03IF8001FF8I01FCL01FEI07CJ0IFI03FFCJ0FFCI07FE,T0KFL01JFEI07FEJ01FFJ03FFEJ07FCJ0FEL01FCI07CJ0FF8J07FEI01FFJ01FF,T0KFL01JFEI07FCK0FF8I03FFCJ03FCJ0FFL01FCI07CI01FFK03FFI01FCK07F8,T0KFL01JFEI0FFL03FCI03FF8J01FEJ07FL03F8I07CI03FCL0FFI03F8K03FC,T0KFL01JFE001FEL01FCI03FEL0FFJ07FL07F8I07CI07FCL07F8003F8K03FC,T0KFL01JFE003FCM0FEI03FEL07FJ03F8K07FJ07CI07F8L03FC003F8K01FC,T0KFL01JFE003F8M07FI03FCL03F8I03F8K07FJ07CI0FFM01FC003F8K01F8,T0KFL01JFE007F8M07FI03FCL03F8I01FCK0FEJ07CI0FEM01FE003F8L0F,T0KFL01JFE007FN03F8003F8L01F8I01FEJ01FEJ07CI0FEN0FE003FC,T0KFL01JFE00FEN03F8003F8L01FCJ0FEJ01FCJ07C001FCN07F003FE,T0KFL01JFE00FEN01F8003FM01FCJ0FEJ03FCJ07C001FCN07F001FF,T0KFL01JFE00FEN01FC003FM01FCJ07FJ03FCJ07C003F8N07FI0FFC,T0KFL01JFE01FEN01FC003FM01FCJ07F8I03F8J07C003F8N03FI0IFC,T0KFL01JFE01FFN03FE003FM01FCJ03F8I07F8J07C003F8N03F8007IF8,T0KFL01JFE01FFDC7E1F03FFE003FN0FCJ03F8I0FFK07C003FO03F8001JF8,T0KFL01JFE01RFE003FN0FCJ03FCI0FFK07C003FO03F8I0KF,T0KFL01JFE01RFE003FN0FCJ01FCI0FEK07C003FO03FCI03JFE,T0KFL01JFE01RFE003FN0FCK0FC001FEK07C003FO03F8J07JF8,T0KFL01JFE01RFC003FN0FCK0FE001FCK07C003FO03F8K0KF,T0KFL01JFE01RFC003FN0FCK0FE003FCK07C003FO03F8K01JFC,T0KFL01JFE01RFI03FN0FCK07F003F8K07C003F8N03F8L07JF,T0KFL01JFE00FFS03FN0FCK07F003F8K07C003F8N03FN01IF,T0KFL01JFE00FES03FN0FCK03F807F8K07C003F8N03FO07FF8,T0KFL01JFE00FES03FN0FCK03F807FL07C003FCN07FO01FFC,T0KFL01JFE00FES03FN0FCK01FC0FEL07C001FCN07FP03FC,T0KFL01JFE007FS03FN0FCK01FC1FEL07C001FCN0FEP01FE,T0KFL01JFE007FS03FN0FCL0FE1FCL07CI0FEN0FEQ0FE,T0KFL01JFE007F8R03FN0FCL0FE3FCL07CI0FFM01FEQ07E,T0KFL01JFE003FCR03FN0FCL07IFCL07CI07FM01FC003CM07E,T0KFL01JFE001FCM078I03FN0FCL07IF8L07CI07F8L03F8007EM0FE,T0KFL01JFE001FEL01FCI03FN0FCL03IFM07CI03FCL07F8007FM0FE,T0KFL01JFEI0FFL03FCI03FN0FCL03IFM07CI03FEK01FFI07F8K01FE,T0KFL01JFEI07FCK0FFCI03FN0FCL01IFM07CI01FF8J03FEI07FCK03FC,T0KFL01JFEI03FFJ03FFCI03FN0FCL01FFEM07CJ0FFCJ0FFCI03FFK0FFC,T0KFL01JFEI03FFCI0IFJ03FN0FCM0FFCM07CJ07FFI03FF8I03FFCI07FF8,T0KFL01JFEJ0IF80IFEJ03FN0FCM0FFCM07CJ03IF03IFK0IF803IF,T0KFL01JFEJ07MFCJ03FN0FCM07FCM07CK0MFEK07MFE,T0KFL01JFEJ03MFK03FN0FCM07F8M07CK07LFCK03MF8,T0KFL01JFEK0LFEK03FN0FCM03F8M07CK01KFEM0MF,T0KFL01JFEK03KFL03FN0FCM03FN07CL07JF8M03KFC,T0KFL01JFEL07IF8L01FN0FCM01EN07CM0IFEO07IFE,T0KFL01JFEM0FFCN0EN0F8N04N018N0FFQ07FE,T0KFL01JFE,:T0KFL01JFC,T0KFL01JF,T0KFL01IF8,T0KFL01FFE,T0KFL01FF,T0KFM0FC,T0KF,T07JF,T03JF,U0JF,U01IF,V07FF,W07E,,::::::::::::::::::::::::::hR03F,::::hR03FM06,hR03FM0F8,hR03FL01FC,hR03FL03FE,hR03FL07FC,hR03FL0FF8,hR03FK01FF,hR03FK03FE,hR03FK07FC,hR03FK0FF8,hR03FJ01FF8,hR03FJ03FE,hR03FJ07FCS0FE,hR03FJ0FF8R01FF,hR03FI01FFS01FF,gI01FFEJ01FCX01FI03FES01FF,gI07IFCI01FCgI07FCS01FF,gH01KFI01FCgI0FF8S01FF,gH07KF8001FCgH01FFT01FF,gH0LFE001FCgH01FET01FF,gG01MF001FCgI0FCT01FF,gG03MF801FCgI078T01FF,gG07FFC0IFC01FCgI03U01FF,gG0FFE001FFC01FCJ0FCR01CY01FF,g01FFCI07FE01FC001IFCL0F8003FFES03F8001FF03EK01F8M0F8,g01FFJ03FF01FC007JF001FC1F800JF8Q03IFC01FF1FFCJ0IF8003FC7FF8,g03FEJ01FF01FC00KFC01FC7F803JFCQ07JF01FF7FFEI03IFE003FDIFC,g03FCK0FF81FC01KFE01FCFF807JFEP01KF81LFI07JF003KFE,g07FCK07F81FC01KFE01FDFF80LFP03KFC1LF800KF803LF,g07F8K03F81FC03FFDIF01JF81LF8O03KF81LFC01KFC03LF8,g07F8N01FC03FC01FF01JF83LFCO07KF01LFC03KFE03LF8,g07FO01FC03FC00FF01IF803FF80FFEO07FE07E01LFE03KFE03LF8,g0FFO01FC07F800FF01FFC007FE003FEO0FF800C01FFC0FFE07FF07FF03FF81FFC,g0FFO01FC07F800FF01FF8007FC001FF01KF80FFK01FF807FE07FC01FF03FF00FFC,g0FFO01FCL0FF01FFI0FF8I0FF01KF80FFK01FF803FE07FC01FF03FE007FC,g0FEO01FCL0FF01FFI0FFJ07F81KF80FF8J01FF003FE0FF800FF03FE007FC,g0FEO01FCK03FF01FEI0FEJ07F81KF80FF8J01FF001FE0FF800FF83FE003FC,g0FEO01FCJ07IF01FEI0FEJ07F81KF80IFEI01FF001FE0FF800FF83FE003FC,g0FFO01FCI0KF01FE001FEJ03F81KF807JF801FF001FE0FF800FF83FE003FC,g0FFO01FC007KF01FC001FEJ03F80KF807JFE01FF001FE0FF800FF83FE003FC,g07FO01FC01LF01FC001FCJ03F8N03KF01FF001FE0FF800FF83FE003FC,g07F8N01FC01IFCFF01FC001FEJ03F8N01KF81FF001FE0FF800FF83FE003FC,g07F8K03F81FC03FF80FF01FC001FEJ03F8O0KFC1FF001FE0FF800FF83FE003FC,g07FCK07F81FC07FC00FF01FCI0FEJ07F8O03JFC1FF001FE0FF800FF83FE003FC,g03FCK0FF81FC07F800FF01FCI0FEJ07F8P01IFE1FF001FE0FF800FF83FE003FC,g03FEJ01FF01FC0FFI0FF01FCI0FFJ07F8R07FE1FF001FE0FF800FF83FE003FC,g01FFJ03FF01FC0FFI0FF01FCI0FF8I0FFS03FE1FF001FE0FF800FF03FE007FC,g01FF8I07FE01FC0FFI0FF01FCI07FC001FFS01FE1FF001FE07F800FF03FE007FC,gG0FFEI0FFE01FC0FFI0FF01FCI07FE003FES01FE1FF001FE07FC01FF03FF007FC,gG07FF807FFC01FC0FF001FF01FCI03FF00FFEO01C003FE1FF001FE07FE03FF03FF00FFC,gG03MF801FC0FF003FF01FCI03LFCO07F007FE1FF001FE03FF07FE03FFC1FF8,gG01MF001FC0FF807FF01FCI01LF8O07KFC1FF001FE03KFE03LF8,gH0LFE001FC07LF01FCJ0LF8N01LFC1FF001FE03KFC03LF8,gH07KFC001FC07LF01FCJ07KFO01LF81FF001FE01KFC03LF,gH03KFI01FC03LF01FCJ03JFEO01LF01FF001FE00KF803LF,gI0JFCI01FC01IFC7F01FCK0JF8P07JFE01FF001FE007JF003KFE,gI01FFEJ01FC007FF07F01FCK03FFEQ03JFC01FF001FE001IFC003FEIFC,gR0FCI0F007F01FCL07FS07IFI0FF001FEI07FFI03FE3FF,iN03EU07J03FE07,jP03FE,::::::::::jP01FC,,:::::::::^FS
                    // ';
                    $file = substr_replace($zpl, $logo,123, 2980);
                    break;
        }
        $file = str_replace("T1 Envios", "", $file);
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

    private function agregarQR($zpl, $guia){
        $zpl = str_replace("^POI", "", $zpl); //se elimina orientación invertida

        $qr = "^FO70,1280
                ^BQN,2,10
                ^FDQA,".$guia."^XZ";

        //se busca la posición de la etiqueta de fin de archivo ^XZ y se reemplaza por el qr
        $finarchivo= strpos($zpl, '^XZ');
        $file = substr_replace($zpl, $qr,$finarchivo, $finarchivo);
        return $file;

    }

    private function modificarDireccion($zpl,$destino){
        $direccion = $this->limitar_cadena($destino->getCalle(), 26, "") . ' ' . $destino->getNumero() . ' ' . $this->clean($destino->getColonia());

        // Cadena a reemplazar desde ^FO61,251 hasta la primera ^FS
        $patron = '/\^FO61,251.*?\^FS/s';

        // Cadena por la que se va a reemplazar
        $direccionCompleta = "^FO61,251^A0N,28,32^FV" . $direccion . "^FS";

        // Realizar el reemplazo
        $resultado = preg_replace($patron, $direccionCompleta, $zpl);
        
        return $resultado;
    }
    
    private function modificarNombre($zpl,$destino,$origen){
        $nombreOrigen = $this->limitar_cadena($origen->getNombre(), 70, "").' '.$this->limitar_cadena($origen->getApellidos(), 70, "");
        $nombreDestino = $this->limitar_cadena($destino->getNombre(), 70, "").' '.$this->limitar_cadena($destino->getApellidos(), 70, "");

        $patronO = '/\^FO15,7.*?\^FS/s';
        $patronD = '/\^FO61,166.*?\^FS/s';
        $patronD2 = '/\^FO61,222.*?\^FS/s';

        // Cadena por la que se va a reemplazar
        $nombreO = "^FO15,7^A0N,20,24^FV" . strtoupper($nombreOrigen) . "^FS";
        $nombreD = "^FO61,166^A0N,28,32^FV" . strtoupper($nombreDestino) . "^FS";
        $nombreD2 = "^FO61,222^A0N,28,32^FV" . strtoupper($nombreDestino) . "^FS";

        // Realizar el reemplazo
        $zpl = preg_replace($patronO, $nombreO, $zpl);
        $zpl = preg_replace($patronD, $nombreD, $zpl);
        $zpl = preg_replace($patronD2, $nombreD2, $zpl);
        $resultado = str_replace("^FO446,9^A0N,30,34^FV","^FO508,9^A0N,30,34^FV", $zpl);//Es para mover un poco el peso

        return $resultado;
    }

}
