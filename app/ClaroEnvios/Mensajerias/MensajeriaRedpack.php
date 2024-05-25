<?php

namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Mensajerias\Accesos\AccesoCampoMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoMultipleMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaResponse;
use App\ClaroEnvios\Uber\TiendaUber;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\ZPL\ZPL;
use GuzzleHttp\Client;
use GuzzleHttp\HandlerStack;
use Sainsburys\Guzzle\Oauth2\GrantType\RefreshToken;
use Sainsburys\Guzzle\Oauth2\GrantType\PasswordCredentials;
use Sainsburys\Guzzle\Oauth2\Middleware\OAuthMiddleware;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use SKAgarwal\GoogleApi\PlacesApi;
use \Illuminate\Support\Facades\Log;
use stdClass;

/**
 * Esta mensajeria es multiusuario la cual permite credenciaales diferentes por cada servicio
 */
class MensajeriaRedpack extends MensajeriaMaster implements MensajeriaCotizable {

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
    private $keys;
    private $tiendaId;
    private $code_response;
    protected $costo_seguro;
    protected $id_servicio;
    protected $config_llaves_servicio;
    protected $id_configuracion;
    private $arrayLabelUrl = [
        'PRODUCCION' => "https://api-v2.redpack.com.mx/redpack/automatic-documentation",
        'TEST' => "https://apiqa.redpack.com.mx:5600/redpack/automatic-documentation",
        'DEV' => "https://apiqa.redpack.com.mx/redpack/automatic-documentation"
    ];
    private $arrayLoginUrl = [
        'PRODUCCION' => "https://api.redpack.com.mx/oauth/token" ,
        'TEST' => "https://api.redpack.com.mx/oauth/token"
    ];
    private $arrayRecolectUrl = [
        'PRODUCCION' => "https://api2.redpack.com.mx/redpack/pickup-request",
        'TEST' => "https://apiqa2.redpack.com.mx:6200/redpack/pickup-request",
        'DEV' => "https://apiqa2.redpack.com.mx/redpack/pickup-request"
    ];


    use AccesoConfiguracionMensajeria;

    public function __construct($mensajeriaTO = false) {

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
            $this->ID = $mensajeriaTO->getId();
            $this->custom = "";
            $this->id_servicio = $mensajeriaTO->getIdServicio();
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();
            
            if (!empty($mensajeriaTO->getCustom())) {
                $this->custom = $mensajeriaTO->getCustom();
            }

            $this->comercioNegociacionID = Auth::user()->id;
            $this->comercioNegociacionID = 1;

            $accesoMultipleMensajeriaTO = new AccesoMultipleMensajeriaTO();
            $accesoMultipleMensajeriaTO->setIdComercio($mensajeriaTO->getComercio());
            $accesoMultipleMensajeriaTO->setIdMensajeria($mensajeriaTO->getId());

            if ($mensajeriaTO->getNegociacionId() == 1) {
                $accesoMultipleMensajeriaTO->setIdComercio(1);
            }

            $this->configurarMultiplesAccesos($accesoMultipleMensajeriaTO);

            if (!$this->configuracion) {
                $this->configuracion = collect();
            }
            Log::info('Ambiente: '.$this->location);
            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                $this->endpointLogin = $this->arrayLoginUrl['PRODUCCION'];
                $this->endpointRecolect = $this->arrayRecolectUrl['PRODUCCION'];
                $this->url_tracking = "https://www.redpack.com.mx/es/rastreo/?guias=";
            } else if($this->location == 'test'){
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->endpointLogin = $this->arrayLoginUrl['TEST'];
                $this->endpointRecolect = $this->arrayRecolectUrl['TEST'];
                $this->url_tracking = "https://www.redpack.com.mx/es/rastreo/?guias=";
            }else{
                $this->endpointLabel = $this->arrayLabelUrl['DEV'];
                $this->endpointLogin = $this->arrayLoginUrl['TEST'];
                $this->endpointRecolect = $this->arrayRecolectUrl['DEV'];
                $this->url_tracking = "https://www.redpack.com.mx/es/rastreo/?guias=";
            }
        }
    }

    /**
     * Otorga una guia
     * @param GuiaMensajeriaTO $guiaMensajeriaTO
     * @return array
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO) {
        $ZPL = new ZPL();
        $destino = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $origen = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $array = [];
        $delivery = $this->createDelivery($destino, $guiaMensajeriaTO, $origen);

        if ($delivery[0]->trackingNumber == null) {
            throw new \Exception("Mensajería responde: " . json_encode($delivery[0]->responseWS));
        }
        $trackingNumber = $delivery[0]->trackingNumber;
        //die(print_r($delivery[0]->parcels[0]->extraData[0]->barcode));
        if (isset($delivery[0]->parcels[0]->extraData[0]->barcode)) {
            $file = $ZPL->convertirZPL($delivery[0]->parcels[0]->extraData[0]->barcode, "pdf", $trackingNumber);
        } else {
            throw new \Exception("Mensajería responde: Error al generar ZPL");
        }

        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath . ('/' . $trackingNumber . '_' . date('YmdHis') . '.' . $this->extension_guia_impresion);
        file_put_contents($rutaArchivo, $file['data']);
        $nombreArchivo = $trackingNumber . '_' . date('YmdHis') . '.pdf';
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file['data']) : base64_encode($file['data']);

        $array['guia'] = $trackingNumber;
        $array['imagen'] = $dataFile;
        $array['extension'] = "pdf";
        $array['nombreArchivo'] = $nombreArchivo;
        $array['ruta'] = $rutaArchivo;
        $array['link_rastreo_entrega'] = $this->url_tracking . "" . $trackingNumber;
        $array['location'] = env('API_LOCATION');
        $array['infoExtra'] = [
            'codigo' => 9,
            'fecha_hora' => Carbon::now()->format('Y-m-d H:i:s'),
            'identificadorUnico' => '',
            'tracking_link' => $this->url_tracking . "" . $trackingNumber
        ];

        if ($guiaMensajeriaTO->getGenerarRecoleccion()) {
            Log::info('Recoleccion REDPACK');
            $recoleccion = $this->recoleccion($guiaMensajeriaTO, $trackingNumber);
            $array['recoleccion'] = $recoleccion;
        }

        return $array;
    }

    /**
     * @param $data
     * @param string $method
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function makeRequest($data, $method = 'POST', $recoleccion=false) {

        try {
            //die(print_r($this->config_llaves_servicio));
            if ($this->config_llaves_servicio->count() >= 6) {
                $token = $this->config_llaves_servicio->get('token');
                $config = [
                    PasswordCredentials::CONFIG_USERNAME => $this->config_llaves_servicio->get('username'),
                    PasswordCredentials::CONFIG_PASSWORD => $this->config_llaves_servicio->get('password'),
                    PasswordCredentials::CONFIG_CLIENT_ID => $this->config_llaves_servicio->get('client_id'),
                    PasswordCredentials::CONFIG_CLIENT_SECRET => $this->config_llaves_servicio->get('client_secret'),
                    PasswordCredentials::CONFIG_TOKEN_URL => $this->endpointLogin,
                ];

                Log::info('Endpoint Login RedPack: '. $this->endpointLogin);
                $oauthClient = new Client(['base_uri' => $this->endpointLogin]);
                $grantType = new PasswordCredentials($oauthClient, $config);
                $refreshToken = new RefreshToken($oauthClient, $config);
                $middleware = new OAuthMiddleware($oauthClient, $grantType, $refreshToken);

                $handlerStack = HandlerStack::create();
                $handlerStack->push($middleware->onBefore());
                $handlerStack->push($middleware->onFailure(5));

                $options = [
                    "json" => $data,
                    'connect_timeout' => 200,
                    'http_errors' => true,
                    'verify' => false,
                    'headers' => [
                        'Content-Type' => 'application/json'
                    ]
                ];

                $client = new Client(['handler' => $handlerStack, 'base_uri' => $this->endpointLogin, 'auth' => 'oauth2']);
                $response='';

                if ($recoleccion) { //si es un consumo de recolección
                    $response = $client->request($method, $this->endpointRecolect, $options);
                }
                else{
                    Log::info('Endpoint Label RedPack: '. $this->endpointLabel);
                    $response = $client->request($method, $this->endpointLabel, $options);
                }


                $statusResponse = $response->getStatusCode();
                $content = $response->getBody()->getContents();
                //die(print_r($content));
                //Datos necesario para guardar log
                $this->setResponse($content);
                $this->setCodeResponse($statusResponse);
                $responseLog = json_decode($content);
                Log::info("JSON RESPUESTA:" . $content);
                return $responseLog;
            }

        } catch (\Exception $exception) {

            Log::error($exception->getMessage());

            if ($exception->getCode() == 401) {
                Log::info("Regresa 401:");

                $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->ID)
                        ->where('clave', 'token')
                        ->first();

                $accesoComercio = AccesoComercioMensajeria::where('mensajeria_id', $this->ID)
                        ->where('comercio_id', $this->comercioNegociacionID)
                        ->where('acceso_campo_mensajeria_id', $accesoCampo->id)
                        ->first();

                if ($accesoComercio) {
                    $tokenActual = $this->config_llaves_servicio->get('token');
                    Log::info("Token actual: " . $tokenActual);

                    $token = $this->getToken('client_credentials');
                    $this->actualizaToken($token);
                    $response = $this->requestRed($data, $method);

                    return $response;
                } else {
                    Log::info("Nuevo Token:");
                    $token = $this->getToken('client_credentials');
                    $this->guardaToken($token, $accesoCampo);

                    $this->config_llaves_servicio->put('token', $token);
                    $response = $this->requestRed($data, $method);

                    return $response;
                }
            } else {
                throw new \Exception($exception->getMessage(), $exception->getCode());
            }
        }
    }

    private function guardaToken($token, $accesoCampo) {

        Log::info("Guarda Token:");
        $date = Carbon::now();
        $acceso = new AccesoComercioMensajeria();
        $acceso->acceso_campo_mensajeria_id = $accesoCampo->id;
        $acceso->mensajeria_id = $this->ID;
        $acceso->comercio_id = $this->comercioNegociaciónID;
        $acceso->valor = $token;
        $acceso->created_at = $date->format('Y-m-d H:i:s');
        $acceso->save();
    }

    private function actualizaToken($token) {
        $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->ID)
                ->where('clave', 'token')
                ->first();
        Log::info("Actualiza Token campo id: " . $accesoCampo->id);

        $acceso = AccesoComercioMensajeria::where('acceso_campo_mensajeria_id', $accesoCampo->id)->where('mensajeria_id', $this->ID)
                ->where('comercio_id', $this->comercioNegociacionID)
                ->firstOrFail();
        $acceso->valor = $token;
        $acceso->update();
    }

    private function getToken($grantType, $token = null) {
        try {

            Log::info("Token actual: " . $token);

            $options = [
                "form_params" => [
                    "username" => $this->config_llaves_servicio->get('email'),
                    "password" => $this->config_llaves_servicio->get('password'),
                    "client_id" => $this->config_llaves_servicio->get('client_id'),
                    "client_secret" => $this->config_llaves_servicio->get('client_secret')
                ],
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/x-www-form-urlencoded',
                    'Accept' => 'application/json'
                ]
            ];


            $client = new Client();
            $response = $client->request("POST", $this->endpointLogin, $options)->getBody()->getContents();

            $result = json_decode($response);
            Log::info("RESPONSE TOKEN:");
            Log::info($response);

            return $result->access_token;
        } catch (\Exception $exception) {
            Log::info("ERROR getToken:");
            Log::info($exception->getMessage());

            throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }

    private function requestRed($data, $method) {

        try {
            $token = $this->config_llaves_servicio->get('token');
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

            $client = new Client();
            $response = $client->request($method, $this->endpointLabel, $options)->getBody()->getContents();
            Log::info("RESPONSE con token:");
            Log::info($response);
            $this->setResponse($response);
            return json_decode($response);
        } catch (\Exception $exception) {
            Log::info("ERROR peticion:");
            Log::info($exception->getMessage());
            throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }

    /**
     * @return mixed
     */
    public function getRequest() {
        return $this->request;
    }

    /**
     * @param mixed $request
     */
    public function setRequest($request): void {
        $this->request = $request;
    }

    /**
     * @return mixed
     */
    public function getResponse() {
        return $this->response;
    }

    /**
     * @param mixed $response
     */
    public function setResponse($response): void {
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
    public function createDelivery(BitacoraMensajeriaDestinoTO $destino, GuiaMensajeriaTO $guiaMensajeriaTO, $origen) {

        Log::info("Entra en createDelivery");
        $usuario = Auth::user()->id;
        Log::info("ID Usuario:" . $usuario);
        $tipoServicio = "";
        $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();

        if ($cotizacion) {
            $tipoServicio = $cotizacion->getTipoServicio();
        } else {
            $tipoServicio = $this->tipo;
        }
        $company = "";
        Log::info("ID Usuario:". $usuario);
        switch ($usuario) {
            case 9:
                $company = "Sears";
                break;
            case 10:
                $company = "Claroshop";
                break;
            case 11:
                $company = "Sanborns";
                break;
            default:
                $company = "Claroshop";
                break;
        }
        Log::info("Tipo Servicio:" . $tipoServicio);
        $pedido = $this->pedido ? "(".$this->pedido.")" :'';
        //die(print_r($this->id_servicio));
        $this->buscarLLavesServicio($tipoServicio);

        $data = [
                    [
                        "deliveryType" => [
                            "id" => 2
                        ],
                        "idClient" => $this->config_llaves_servicio->get('idClient'),
                        "origin" => [
                            "city" => $this->clean( $origen->getMunicipio() ),
                            "company" => $company,
                            "country" => "MX",
                            "email" => $origen->getEmail(),
                            "externalNumber" => $origen->getNumero(),
                            "internalNumber" => "",
                            "name" => $this->clean("{$origen->getNombre()} {$origen->getApellidos()}"),
                            "phones" => [
                                [
                                    "areaCode" => "+52",
                                    "extension" => "",
                                    "phone" => $origen->getTelefono()
                                ]
                            ],
                            "reference3" => $this->clean( $origen->getReferencias() ),
                            "state" => $this->clean( $origen->getEstado() ),
                            "street" => $this->clean( $origen->getCalle() ),
                            "suburb" => $this->clean( $origen->getColonia() ),
                            "zipCode" => $this->codigo_postal_origen,
                            "originRfc" => "PVC0712146V9" //RFC Claroshop
                        ],
                        "parcels" => [
                            [
                                "description" => $this->clean( $guiaMensajeriaTO->getContenido() ),
                                "high" => (int) $this->alto,
                                "length" => (int) $this->largo,
                                "piece" => 1,
                                "weigth" => (float) $this->peso,
                                "width" => (int) $this->ancho
                            ]
                        ],
                        "printType" => 2,
                        "reference2" => "",
                        "serviceType" => [
                            "id" => ($tipoServicio == 'EXPRESS') ? 1 : 2
                        ],
                        "shippingType" => [
                            "id" => ($this->tipo_paquete == "1") ? 2 : 1
                        ],
                        "target" => [
                            "city" => $this->clean( $destino->getMunicipio() ),
                            "company" => $this->clean( $pedido."{$destino->getNombre()} {$destino->getApellidos()}" ),
                            "country" => "MX",
                            "email" => $destino->getEmail(),
                            "externalNumber" => $destino->getNumero(),
                            "internalNumber" => "",
                            "name" => $this->clean( "{$destino->getNombre()} {$destino->getApellidos()}" ),
                            "phones" => [
                                [
                                    "areaCode" => "+52",
                                    "extension" => "",
                                    "phone" => $destino->getTelefono()
                                ]
                            ],
                            "reference1" => $this->clean( $destino->getReferencias() ),
                            "state" => $this->clean( $destino->getEstado() ),
                            "street" => $this->clean( $destino->getCalle() ),
                            "suburb" => $this->clean( $destino->getColonia() ),
                            "zipCode" => $this->codigo_postal_destino
                        ]

                    ]
        ];
        Log::info("JSON GUIA" . json_encode($data));
        $this->setRequest(json_encode($data));
        $response = $this->makeRequest($data);
       // die(print_r($response));

        Log::info("Orden creada");
        return $response;
    }

    private function generateFile($data) {

        try {

            $counter = $data['0']->reason->counter;

            $token = $this->config_llaves_servicio->get('apikey');

            $request = [
                "counter" => [
                    $counter
                ],
                "base64" => false,
                "size" => "zebra"
            ];

            $options = [
                "json" => $request,
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Authorization' => 'Bearer ' . $token
                ]
            ];

            Log::info("GUIA ZPL:");
            Log::info($counter);


            $client = new Client();
            $response = $client->request('POST', $this->endpointLabel . "guide/order", $options)->getBody()->getContents();

            Log::info("RESPONSE ZPL");

            if ($response) {
                return $response;
            } else {

                Log::info("ERROR peticion PDF Redpack:");

                throw new \Exception("No hay respuesta de PDF Redpack");
            }
        } catch (\Exception $exception) {
            Log::info("ERROR peticion ZPL:");
            Log::info($exception->getMessage());

            throw new \Exception($exception->getMessage(), $exception->getCode());
        }
    }

    private function buscarLLavesServicio($tipoServicio){
        $configuracionLlaves = ($this->configuracion->where('id_servicio',$this->id_servicio)->pluck('valor','accesoCampoMensajeria.clave'));
        if($configuracionLlaves->count() == 0){
            throw new \Exception("No cuenta con credenciales para el servicio: ".$tipoServicio);
            $this->config_llaves_servicio = null;
        }else{
            $this->config_llaves_servicio = $configuracionLlaves;
        }
    }

    public function getTipoServicio() {

    }

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO, $guia = '') {
        try {
            $origen = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();

            $date = Carbon::now()->toAtomString();
            $usuario = Auth::user()->id;
            $company = "";
            Log::info("ID Usuario:". $usuario);
            switch ($usuario) {
                case 9:
                    $company = "Sears";
                    break;
                case 10:
                    $company = "Claroshop";
                    break;
                case 11:
                    $company = "Sanborns";
                    break;
                default:
                    $company = "Claroshop";
                    break;
            }
            $data = [
                [
                   "trackingNumber"=>$guia,
                   "name"=>$this->clean("{$origen->getNombre()} {$origen->getApellidos()}"),
                   "origin"=>[
                      "company"=> $company,
                      "email"=>$origen->getEmail(),
                      "street"=>$this->clean( $origen->getCalle() ),
                      "externalNumber"=>$origen->getNumero(),
                      "internalNumber"=>"",
                      "suburbId"=>$this->codigo_postal_origen,
                      "phones"=>[
                        [
                            "phone"=>$origen->getTelefono()
                        ]
                      ]
                    ],
                   "weight"=>(float) $this->peso,
                   "envelopesCount"=>[
                      "count"=>($this->tipo_paquete == "1") ? 1 : 0
                    ],
                   "packagesCount"=>[
                      "count"=>($this->tipo_paquete == "2" || $this->tipo_paquete == null) ? 1 : 0
                   ],
                   "pickupDate"=>$date,
                   "dimensions"=>$this->alto."X".$this->largo."X".$this->ancho
                ]
            ];

            Log::info("JSON RECOLECCIÓN GUIA" . json_encode($data));
            $response = $this->makeRequest($data,'POST',true);

            Log::info("RECOLECCIÓN creada");

            $recoleccion = new stdClass();
            $recoleccion->status = 'Success';
            $recoleccion->mensaje = 'Confirmacion de exitosa';
            $recoleccion->location = $this->location;
            if ($response['0']->pickupRequestNumber==null) {
                $condition = $response['0']->responseWS['0'];
                $recoleccion->status = 'Error';
                $recoleccion->mensaje = $condition->response;
                return $recoleccion;
            }
            $recoleccion->pick_up = $response['0']->pickupRequestNumber;
            $recoleccion->localizacion = "";
            $recoleccion->request = json_encode($data);
            $recoleccion->response =json_encode($response);

            return $recoleccion;

        } catch (\Exception $exception) {
            Log::info("ERROR peticion ZPL:");
            Log::info($exception->getMessage());

            throw new \Exception($exception->getMessage(), $exception->getCode());

        }
    }

    
    public function recoleccionMensajeria(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO, $guia = '') {
        try {

            throw new \Exception('Recoleccion no disponible por el momento');

           // return json_encode(['success'=>false,"message"=>,'localizacion'=>false]);

        } catch (\Exception $exception) {
           
            Log::info($exception->getMessage());

            throw new \Exception($exception->getMessage(), $exception->getCode());

        }
    }

    public function validarCampos() {

    }

    public function verificarExcedente($response) {

    }

    public function rate($traerResponse = false) {

    }

    public function getCodeResponse() {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void {
        $this->code_response = $codeResponse;
    }

    public function limitar_cadena($cadena, $limite, $sufijo) {
        if (strlen($cadena) > $limite) {
            return substr($cadena, 0, $limite) . $sufijo;
        }

        return $cadena;
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
