<?php
namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use GuzzleHttp\Client;
use \Illuminate\Support\Facades\Log;
use DateTime;
use stdClass;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;


class MensajeriaPaqueteriaExpress extends MensajeriaMaster implements MensajeriaCotizable
{

    protected $peso;
    protected $largo;
    protected $ancho;
    protected $alto;
    protected $codigo_postal_origen;
    protected $codigo_postal_destino;
    protected $extension_guia_impresion;
    protected $request;
    protected $response;
    protected $location;
    private $endpointLabel;
    private $endpointLogin;
    private $endpointGetZPL;
    private $endpointCancelarGuia;
    private $endpointRecoleccionGuia;
    private $endpointDescargaPDF;

    private $endpointRate;
    private $id;
    private $comercioNegociacionID;
    private $custom;
    private $pedido;

    private $tiendaId;
    private $code_response;
    private $tienda_nombre;

    protected $id_configuracion;
    private $idProductSat;

    //PARA GENERAR GUIA
    private $billRad = "REQUEST";
    private $billClntId = "26689366";
    private $pymtMode = "PAID";
    private $pymtType = "C";
    private $comt = "";

    private $addrLin1 = "Mexico";
    private $addrLin3 = "Mexico";
    private $suitNo = 10;
    private $addrType = "ORIGIN";

    private $addrLin1Dest = "Mexico";
    private $addrLin3Dest = "CH";
    private $addrTypeDest = "DESTINATION";

    private $srvcId = "PACKETS";
    private $cont = "PAQUETE DESCRIPCION";
    private $qunt = 1;

    private $srvcIdItem = "RAD";
    private $srvcIdItemEad = "EAD";
    private $value1 = "";
    private $srvcChrg = "";
    private $disc = "";
    private $tax = "";
    private $taxRet = "";

    private $typeSrvcId = "STD-T";

    private $grGuiaRefr = "";

    private $fechaRecoleccion;
    private $horaFrom = "07:00:00";
    private $horaTo = "17:00:00";
    private $ID;


    private $ordenRecoleccion;
    private $content;
    private $productosRecibidos;
    private $idMensajeria;

    private $requestRecoleccion;
    private $responseRecoleccion;

    //Valores para rate(tarificador)
    private $rateURL = [
        "PRODUCCION" => "https://cc.paquetexpress.com.mx/WsQuotePaquetexpress/api/apiQuoter/v2/getQuotation",
        "TEST" => "http://qaglp.paquetexpress.mx:7007/WsQuotePaquetexpress/api/apiQuoter/v2/getQuotation"
        //"TEST" => "http://qaglp.paquetexpress.mx/WsQuotePaquetexpress/api/apiQuoter/v2/getQuotation"
    ];

    protected $costo;
    protected $porcentaje;
    protected $seguro;
    protected $porcentaje_seguro;
    protected $valor_paquete;
    protected $paquetes;
    protected $negociacion_id;
    protected $dias_embarque;
    protected $negociacion;
    protected $porcentaje_calculado;
    protected $costo_seguro;

    use AccesoConfiguracionMensajeria;


    public function __construct($mensajeriaTO = false)
    {

        if ($mensajeriaTO instanceof MensajeriaTO) {
            $this->location = env('API_LOCATION', 'test');

            $this->pedido = $mensajeriaTO->getPedido();
            
            $this->peso = $mensajeriaTO->getPeso();
            $this->largo = $mensajeriaTO->getLargo();
            $this->ancho = $mensajeriaTO->getAncho();
            $this->alto = $mensajeriaTO->getAlto();
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();
            
            $this->codigo_postal_origen = $mensajeriaTO->getCodigoPostalOrigen();
            $this->codigo_postal_destino = $mensajeriaTO->getCodigoPostalDestino();
            
            $this->extension_guia_impresion = $mensajeriaTO->getExtensionGuiaImpresion();

            $this->tiendaId = $mensajeriaTO->getTiendaId();
            $this->tienda_nombre = $mensajeriaTO->getTiendaNombre();

            $this->ID = $mensajeriaTO->getId();
            $this->custom = "";
            if(!empty($mensajeriaTO->getCustom())){
                $this->custom = $mensajeriaTO->getCustom();
            }
            //Valores para rate
            $this->costo             = $mensajeriaTO->getCosto();
            $this->porcentaje        = $mensajeriaTO->getPorcentaje();
            $this->seguro            = $mensajeriaTO->getSeguro();
            $this->valor_paquete     = $mensajeriaTO->getValorPaquete();
            $this->porcentaje_seguro = $mensajeriaTO->getPorcentajeSeguro();
            $this->paquetes          = $mensajeriaTO->getPaquetes();
            $this->negociacion_id    = $mensajeriaTO->getNegociacionId();
            $this->dias_embarque     = $mensajeriaTO->getDiasEmbarque();
            $this->negociacion       = $mensajeriaTO->getNegociacion();
            $this->id_configuracion  = $mensajeriaTO->getIdConfiguracion();
            $this->costo_seguro      = $mensajeriaTO->getCostoSeguro();


            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());


            //SI es negiciación 1-t1envios se toman las llaves de mensajeria de t1envios
            if($mensajeriaTO->getNegociacionId() == 1 ){
                $accesoComercioMensajeriaTO->setComercioId(1);
                $this->comercioNegociacionID = 1;
            }else{
                $this->comercioNegociacionID = $mensajeriaTO->getNegociacionId();
            }

            Log::info("COMERCIO ID: ".$accesoComercioMensajeriaTO->getComercioId());
            Log::info("MENSAJERIA ID: ".$accesoComercioMensajeriaTO->getMensajeriaId());
            $this->idMensajeria = $accesoComercioMensajeriaTO->getMensajeriaId();
            $this->configurarAccesos($accesoComercioMensajeriaTO);

            if(!$this->configuracion){
                $this->configuracion = collect();
            }

            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointRate = $this->rateURL["PRODUCCION"];
            } else {
                $this->endpointRate = $this->rateURL["TEST"];
            }
        }
    }
    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO)
    {

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


    public function getCodeResponse()
    {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void
    {
        $this->code_response = $codeResponse;
    }


    private function makeRequestGeneric(
        $mensajeLog
        , $data
        , $urlEndpoint
        , $mostrarLogsData = TRUE
        , $mostrarLogsRespuesta = FALSE)
    {
        try{
            $logMessage = "-------------------- Inicia makeRequestGeneric -------------------" . PHP_EOL;
            if($mostrarLogsData){
                $logMessage .= "LA DATA PARA ".$mensajeLog." ES: ".$data .PHP_EOL;
            }
            
            $options = [
                        "json" => json_decode($data),
                        'connect_timeout' => 90,
                        'http_errors' => true,
                        'verify' => false,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Accept' => 'application/json'
                        ]
            ];

            $client = new Client();
            $response = $client->request("POST", $urlEndpoint, $options);

            $respuesta = [
                "contenido"=> $response->getBody()->getContents(),
                "code" => $response->getStatusCode()
            ];

            if($mostrarLogsRespuesta){
                $logMessage .= "RESPUESTA ".$mensajeLog." ES:" .PHP_EOL;
                $logMessage .= json_encode($respuesta) .PHP_EOL;
            }
            Log::info($logMessage);
            return $respuesta;

        }catch (\Exception $exception){
                Log::error("ERROR ".$mensajeLog." ES: ");
                Log::error($exception->getMessage());
                $respuesta = [
                    "contenido"=> $exception->getMessage(),
                    "code" => $exception->getCode()
                ];
                Log::error("Respuesta:".json_encode($respuesta));
                return $respuesta;
                //throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }


    public function rate($traerResponse = false, $peso = null)
    {
        $logMessage = "ENTRANDO AL RATE DE PAQUETERIAEXPRESS" .PHP_EOL;
        try{
            
            //$logMessage .= "TOKENPE: ".$this->configuracion->get('token'). PHP_EOL;
            //$token = "F9C9344EB5992637E053350AA8C09787";
            $data = $this->payloadCotizador($this->configuracion->get('token'));
            //$logMessage .= "DATAPE: ".json_encode($data). PHP_EOL;
            
            $response = $this->makeRequestGeneric("SOLICITAR COTIZADOR", $data, $this->endpointRate, FALSE);

            $arrayServiciosPermitidos = [
                'Standard'
            ];
            $logMessage .= "RESPONSE RATE PAQUETERIAEXPRESS:" .PHP_EOL;
            $logMessage .= json_encode($response) .PHP_EOL;
            $statusResponse = $response["code"];
            $this->content = $response["contenido"];
            //Datos necesario para guardar log
            $this->setCodeResponse($statusResponse);
            $responseLog = json_decode($this->content);
            $this->setRequest($data);
            $tarificador = new stdClass();
            $tarificador->success = true;
            $tarificador->message = Response::$messages['successfulSearch'];
            if ($traerResponse) {
                $tarificador->request = json_encode($data);
                $tarificador->response = json_encode($responseLog);
                $tarificador->code_response = $this->getCodeResponse();
            }
            
            if($statusResponse == 200 && $responseLog->body->response->data != null){
                $rateReply = $responseLog->body->response->data;
                $tipo_service = $rateReply->quotations[0]->id;
                $costo_claro  = $rateReply->quotations[0]->amount->totalAmnt;
                //error_log("costo_claro:".$costo_claro);
                $this->code_response = 200;
                $logMessage .= 'RateReplyDetails' .PHP_EOL;
                $logMessage .= 'DATA RATE' .PHP_EOL;
                $logMessage .= json_encode($rateReply) .PHP_EOL;
                $logMessage .= 'paso' .PHP_EOL;
                $logMessage .= 'Service type' .PHP_EOL;
                $logMessage .= $tipo_service .PHP_EOL;
                $logMessage .= 'Entra en RateReplyDetails: ' . $tipo_service .PHP_EOL;
                if (in_array($tipo_service, $arrayServiciosPermitidos)) {
                    $logMessage .= 'Entra en servicios permitidos: ' . $tipo_service .PHP_EOL;
                }

                if (!property_exists($tarificador, 'servicios')) {
                    $logMessage .= 'No existe servicios' .PHP_EOL;
                    $tarificador->servicios = new stdClass();
                }
                $costoAdicional = 0;
                $costoSeguro = 0;

                if ($this->costo != 0) {
                    $costoAdicional = $this->costo;
                    $logMessage .= ' Costo margen: ' . $this->costo .PHP_EOL;
                } elseif ($this->porcentaje != 0) {
                    $costoAdicional = round($costo_claro * ($this->porcentaje / 100), 2);
                    $logMessage .= ' Porcentaje margen: ' . $this->porcentaje .PHP_EOL;
                }

                $logMessage .= ' Costo guia mensajeria EXPRESS: ' . $costo_claro .PHP_EOL;
                if (in_array($this->id_configuracion, ConfiguracionComercio::$comerciosZonas)) {
                    $logMessage .= ' Calculo zonas: ' .PHP_EOL;
                    $costoGuia = $costo_claro;
                    $costoTotalCalculado = round(($costoGuia / (1 - ($this->porcentaje / 100))), 2);
                    $logMessage .= 'Costo Total zonas: ' . $costoTotalCalculado .PHP_EOL;
                } else {
                    $logMessage .= ' Calculo default: ' .PHP_EOL;
                    $costoSeguro = $this->seguro ? round($this->valor_paquete * ($this->porcentaje_seguro / 100), 2) : 0;
                    $logMessage .= ' Costo adicional calculado: ' . $costoAdicional .PHP_EOL;
                    $logMessage .= ' Costo Seguro ' . $costoSeguro .PHP_EOL;
                    $costoTotalCalculado = round($costo_claro + $costoAdicional + $costoSeguro, 2);
                    $logMessage .= ' Costo Total: ' . $costoTotalCalculado .PHP_EOL;
                }

                $totalPaquetes = 0;
                if ($this->paquetes){
                    $totalPaquetes = $this->paquetes;
                }
                $servicioMensajeria = $this->obtenerServicioMensajeria('Express', $tipo_service);
                $servicio = $this->responseService($costo_claro, $costo_claro, $costoTotalCalculado, $servicioMensajeria, null, $costoSeguro,false ,$totalPaquetes);
                $tarificador->servicios->{$tipo_service} = $servicio;
                $tarificador->location = (env('API_LOCATION') == 'test')?$this->endpointRate:env('API_LOCATION');
                $tarificador->code_response = $this->code_response;         
                
            } else {
                if($statusResponse != 404){
                    //Log::info('Erorr else1 tarificador Servicios PaqueteriaExpress');
                    $tarificador->success = false;
                    $tarificador->code_response = $responseLog->body->response->messages[0]->code;
                    $tarificador->servicios = new stdClass();
                    $tarificador->message = $responseLog->body->response->messages[0]->description;
                } else {
                    //Log::info('Erorr else2 tarificador Servicios PaqueteriaExpress');
                    $tarificador->success = false;
                    $tarificador->code_response = $statusResponse;
                    $tarificador->servicios = new stdClass();
                    $tarificador->message = $this->content;
                }
            }
            

        }catch (\Exception $exception){
            Log::error("ERROR CATCH tarificador PaqueteriaExpress :".$exception->getMessage()."-".$exception->getCode());
            $tarificador->success = false;
            $tarificador->code_response = $exception->getCode();
            $tarificador->servicios = new stdClass();
            $tarificador->message = $exception->getMessage();
            //throw new \Exception($exception->getMessage(),$exception->getCode());
        }        
        $logMessage .= 'termina cotizacion Express' .PHP_EOL;
        Log::info($logMessage);
        return $tarificador;
    }

    private function payloadCotizador($token) 
    {
        $data = '
            {
                "header": {
                    "security": {
                        "user": "'.$this->configuracion->get('usuario').'",
                        "password": "'.$this->configuracion->get('password').'",
                        "type": 1,
                        "token": "'.$token.'"
                    },
                    "device": {
                        "appName": "t1envios",
                        "type": "API",
                        "ip": "",
                        "idDevice": ""
                    },
                    "target": {
                        "module": "QUOTER",
                        "version": "1.0",
                        "service": "quoter",
                        "uri": "quotes",
                        "event": "R"
                    },
                    "output": "JSON",
                    "language": null
                },
                "body": {
                    "request": {
                        "data": {
                            "clientAddrOrig": {
                                "zipCode": "'.$this->codigo_postal_origen.'",
                                "colonyName": "  "
                            },
                            "clientAddrDest": {
                                "zipCode": "'.$this->codigo_postal_destino.'",
                                "colonyName": "  "
                            },
                            "services": {
                                "dlvyType": "1",
                                "ackType": "N",
                                "totlDeclVlue": '.$this->valor_paquete.',
                                "invType": "N",
                                "radType": "1"
                            },
                            "otherServices": {
                                "otherServices": []
                            },
                            "shipmentDetail": {
                                "shipments": [
                                    {
                                        "sequence": 1,
                                        "quantity": 1,
                                        "shpCode": "2",
                                        "weight": '.$this->peso.',
                                        "volume": "",
                                        "longShip": '.$this->largo.',
                                        "widthShip": '.$this->ancho.',
                                        "highShip": '.$this->alto.'
                                    }
                                ]
                            },
                            "quoteServices": [
                                "ST"
                            ]
                        },
                        "objectDTO": null
                    },
                    "response": null
                }
            }
        ';
        return $data;
    }

    public function getTipoServicio(){
        
    }

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO, $guia = '') 
    {

    }

    public function validarCampos()
    {
        
    }

    public function verificarExcedente($response)
    {

    }

}