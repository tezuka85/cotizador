<?php
namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Respuestas\Response;
use App\Http\Controllers\Api\SoapGeneral\InstanceSoapClient;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use App\Exceptions\ValidacionException;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponse;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\GuiaExcedente\GuiaExcedente;

class MensajeriaEstafeta extends MensajeriaMaster implements MensajeriaCotizable
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
    private $endpointTarificador;
    protected $porcentaje_calculado;
    protected $costo_calculado;
    protected $seguro;

    private $keys;

    private $arrayRateUrl = [
        'PRODUCCION'=>"http://frecuenciacotizador.estafeta.com/Service.asmx?WSDL",
        'TEST'=>"http://frecuenciacotizador.estafeta.com/Service.asmx?WSDL"
    ];
    private $arrayLabelUrl = [
        'PRODUCCION'=>"https://label.estafeta.com/EstafetaLabel20/services/EstafetaLabelWS?wsdl",
        'TEST'=>"https://labelqa.estafeta.com/EstafetaLabel20/services/EstafetaLabelWS?wsdl"
    ];
    private $arraySearchLabelUrl = [
        'PRODUCCION'=>"https://tracking.estafeta.com/Service.asmx?wsdl",
        'TEST'=>"https://trackingqa.estafeta.com/Service.asmx?wsdl"
    ];

    use AccesoConfiguracionMensajeria;
    /**
     * MensajeriaFedex constructor.
     */
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

            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());
            
            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                $this->endpointTarificador = $this->arrayRateUrl['PRODUCCION'];
                $this->endpointConsulta = $this->arraySearchLabelUrl['PRODUCCION'];

                if($mensajeriaTO->getNegociacionId() == 2){
                    $this->configurarAccesos($accesoComercioMensajeriaTO);
                }

                $this->keys = $this->getPoduccionKeys();
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->endpointTarificador = $this->arrayRateUrl['TEST'];
                $this->endpointConsulta = $this->arraySearchLabelUrl['TEST'];
                $this->keys = $this->getTestKeys();
            }
        }
    }
    // Otorga una guia en PDF
    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO){
        $destino = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $origen = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
            self::setWsdl($this->endpointLabel);
            $this->service = MensajeriaMaster::initSoapClient();
            
            $contentido = [
                'customerNumber' => $this->keys->get('customerNumber'),
                'login' => $this->keys->get('login'),
                'password' => $this->keys->get('password'),
                'suscriberId' => $this->keys->get('suscriberId'),
                'labelDescriptionListCount' => 1,
                'paperType' => 1,
                'quadrant' => 0,
                'valid' => True,
                'labelDescriptionList' => [
                    'content' => $guiaMensajeriaTO->getContenido(),
                    'deliveryToEstafetaOffice' => False,
                    'destinationInfo' => [
                        'address1' => $destino->getDireccionCompuesta(),
                        'city' => $destino->getMunicipio(),
                        'contactName' => "{$destino->getNombre()} {$destino->getApellidos()}",
                        'corporateName' => "{$destino->getNombre()} {$destino->getApellidos()}",
                        'customerNumber' => $this->keys->get('customerNumber'),
                        'neighborhood' => $destino->getColonia(),
                        'phoneNumber' => $destino->getTelefono(),
                        'state' => $destino->getEstado(),
                        'valid' => True,
                        'zipCode' => $cotizacion->getCodigoPostalDestino()
                    ],
                    'numberOfLabels' => 1,
                    'officeNum' => '130',
                    'originInfo' => [
                        'address1' => $origen->getDireccionCompuesta(),
                        'city' => $origen->getMunicipio(),
                        'contactName' => "T1 Envios",
                        'corporateName' => "T1 Envios",
                        'customerNumber' => $this->keys->get('customerNumber'),
                        'neighborhood' => $destino->getColonia(),
                        'phoneNumber' => $destino->getTelefono(),
                        'state' => $destino->getEstado(),
                        'valid' => True,
                        'zipCode' => $cotizacion->getCodigoPostalOrigen(),
                    ],
                    'originZipCodeForRouting' => $cotizacion->getCodigoPostalDestino(),
                    'parcelTypeId' => ($cotizacion->getTipoPaquete() === 1) ? 1 : 4,
                    'returnDocument' => False,
                    'serviceTypeId' => '70',
                    'valid' => True,
                    'weight' => (int) $cotizacion->getPeso()
                ]
            ];
            $label = $this->service->createLabel($contentido);
          
            if ($label->globalResult->resultCode !== 0) {
                $codigo = $label->globalResult->resultCode;
                $mensaje = $label->globalResult->resultDescription;
              
                throw new ValidacionException($codigo.': '.$mensaje);
            }
            $arrayLabel = $this->convertObjectToArray($label);
            $response["guia"] = $arrayLabel["labelResultList"][0]["resultDescription"];
            $response["location"] = (env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION');
            $response["imagen"] = $arrayLabel["labelPDF"]; 
            $response["extension"] = "pdf";
            $response = array_map("utf8_encode", $response );
            $this->request = $this->service->__getLastRequest();
            $this->response = $this->service->__getLastResponse();
            if ($guiaMensajeriaTO->getGenerarRecoleccion()) {
               
                $response['recoleccion'] = $this->recoleccion($guiaMensajeriaTO);
            }
            return $response;
       
    }

    // consulta precios de envios 
    public function rate($traerResponse = false){
        $tarificador = new \stdClass();
        self::setWsdl($this->endpointTarificador);
        $this->service = MensajeriaMaster::initSoapClient();
        $contentido = [
            'idusuario' => $this->keys->get('idusuario'),
            'usuario' => $this->keys->get('usuario'),
            'contra' => $this->keys->get('contra'),
            'esFrecuencia' => false,
            'esLista' => true,
            'tipoEnvio' => [
                'EsPaquete' => ($this->tipo_paquete === 1) ? false : true , //si es True debe tener las dimensiones porque es paquete
                'Largo' => $this->largo,
                'Peso' => $this->peso,
                'Alto' => $this->alto,
                'Ancho' => $this->ancho
            ],
            'datosOrigen' => [
                $this->codigo_postal_origen
            ],
            'datosDestino' => [
                $this->codigo_postal_destino 
            ]
        ];
        $response = $this->service->FrecuenciaCotizador($contentido);
        $getResponse = $response->FrecuenciaCotizadorResult->Respuesta;
//        die(print_r($response ));
        if (property_exists($getResponse, 'TipoServicio')) {

                $tarificador->success = true;
                $tarificador->message = Response::$messages['successfulSearch'];
                if ($traerResponse) {
                    $tarificador->request = $this->service->__getLastRequest();
                    $tarificador->response = $this->service->__getLastResponse();
                }

                foreach ($response->FrecuenciaCotizadorResult->Respuesta->TipoServicio->TipoServicio as $data) {
                        
                    if (!property_exists($tarificador, 'servicios')) {
                        $tarificador->servicios = new \stdClass();
                    }
                    $costo = $data->CostoTotal;
                    $costoAdicional = 0;

                    if ($this->costo != 0) {
                        $costoAdicional = $this->costo;
                    }elseif ($this->porcentaje != 0){
                        $costoAdicional = round($data->CostoTotal*($this->porcentaje/100), 4);
                    }
                    $costoSeguro = $this->seguro?round($this->valor_paquete*($this->porcentaje_seguro/100), 4):0;
                    Log::info(' Costo Serguro '.$costoSeguro);
                    Log::info(' Costo Adicional '.$costoAdicional);

//                    die(print_r($response->FrecuenciaCotizadorResult->Respuesta->TipoServicio));
                    $servicioMensajeria = $this->obtenerServicioMensajeria('Estafeta',$data->DescripcionServicio);
                    $costoTotalCalculado =  round($data->CostoTotal + $costoAdicional + $costoSeguro, 4);
                    $servicio = $this->responseService($costo,$costo,$costoTotalCalculado, $servicioMensajeria);

                    $tarificador->servicios->{$data->DescripcionServicio} = $servicio;
                    $tarificador->location  = (env('API_LOCATION') == 'test')?$this->endpointTarificador:env('API_LOCATION');
                }
                if (property_exists($tarificador, 'servicios')) {
                    return $tarificador;
                }
            }
            $tarificador->status = 'error';
            $tarificador->codigo = $response->FrecuenciaCotizadorResult->Respuesta->Error;
            $tarificador->message = $response->FrecuenciaCotizadorResult->Respuesta->MensajeError;
            return $tarificador;

    }

    private function getTestKeys(){

        return collect([
            "customerNumber" => '0000000',
            "login" => 'prueba1',
            "password" => 'lAbeL_K_11',
            "suscriberId" => 28,
            "idusuario" => 1,
            "usuario" => 'AdminUser',
            "contra" => ',1,B(vVi',
            "rastreo_suscriberId" => 25,
            "rastreo_login" => "Usuario1",
            "rastreo_password" => "1GCvGIu$"
        ]);
    }

    private function getPoduccionKeys(){

        return collect([
            "customerNumber" => '0000000',
            "login" => 'PromologUser',
            "password" => '0C$d0KOY',
            "suscriberId" => 26,
            "idusuario" => 1,
            "usuario" => 'AdminUser',
            "contra" => ',1,B(vVi',
            "rastreo_suscriberId" => 25,
            "rastreo_login" => "Usuario1",
            "rastreo_password" => "1GCvGIu$"
        ]);
    }

    function convertObjectToArray($data) {
        if (is_object($data)) {
            $data = get_object_vars($data);
        }
    
        if (is_array($data)) {
            return array_map(__METHOD__, $data);
        }
        else {
            return $data;
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

    public function validarCampos(){
        $rules = [
            "customerNumber" => "required",
            "login" => "required",
            "password" => "required",
            "suscriberId" => "required",
        ];

        return $rules;
    }

    //métodos a implementar
    public function rastreoGuia()
    {
        $responseTrack = New ResponseTrack();
        self::setWsdl($this->endpointConsulta);
        $this->service = InstanceSoapClient::init();
        $data = [
            "suscriberId" => $this->keys->get('rastreo_suscriberId'),
            "login" => $this->keys->get('rastreo_login'),
            "password" => $this->keys->get('rastreo_password'),
            "searchType" => [
                "waybillList" => [
                    "waybillType" => "G",
                    "waybills" => [$this->guia_mensajeria->guia]
                ],
                "type" => "L"
            ],
            "searchConfiguration" => [
                "includeDimensions" => 1,
                "includeWaybillReplaceData" => 0,
                "includeReturnDocumentData" => 0,
                "includeMultipleServiceData" => 0,
                "includeInternationalData" => 0, 
                "includeSignature" => 0, 
                "includeCustomerInfo" => 1,
                "historyConfiguration" => [
                    "includeHistory" => 1,
                    "historyType" => "ALL"
                ],
                "filterType" => [
                    "filterInformation" => 0
                ]
            ]
        ];
        
        $response = $this->service->ExecuteQuery($data);
//        die(print_r($response));
        if ($response->ExecuteQueryResult->errorCode != 0 ) {
            $codigo = $response->ExecuteQueryResult->errorCode;
            $mensaje = $response->ExecuteQueryResult->errorCodeDescriptionSPA;
            
            throw new ValidacionException($codigo.': '.$mensaje);
        }
        self::setRequest($this->service->__getLastRequest());
        self::setResponse($this->service->__getLastResponse());

        if (!isset($response->ExecuteQueryResult->trackingData->TrackingData)) {
                
            $responseTrack->setRequest($this->service->__getLastRequest());
            $responseTrack->setResponse($this->service->__getLastResponse());
            $responseTrack->setActualiza(false);
            $responseTrack->setTrack("Guia aún sin movimientos");
            return  $responseTrack;
        
        
        }

        $this->verificarExcedente($response);
        
        $datosTracking = $response->ExecuteQueryResult->trackingData->TrackingData;

        $rastreo = new \stdClass();
        $rastreo->location = (env('API_LOCATION') == 'test')?$this->endpointConsulta:env('API_LOCATION');
        
        $rastreo->guia = $datosTracking->waybill;
        $rastreo->status = $datosTracking->statusSPA;
        $rastreo->codigo_ubicacion_origen = $datosTracking->pickupData->originAcronym;
        $rastreo->ubicacion_origen = $datosTracking->pickupData->originName;
        $rastreo->codigo_ubicacion_destino = $datosTracking->deliveryData->destinationAcronym;
        $rastreo->ubicacion_destino = $datosTracking->deliveryData->destinationName;
        $rastreo->fecha_envio = $datosTracking->pickupData->pickupDateTime;
        
        $arrayEventos = [];
        foreach ($datosTracking->history->History as $eventoEnvio) {
            
            $evento = new \stdClass();
            $evento->fecha_entrega = $eventoEnvio->eventDateTime;
            $evento->codigo_evento = $eventoEnvio->eventId;
            $evento->evento = $eventoEnvio->eventDescriptionSPA;
            $evento->codigo_ubicacion = $eventoEnvio->eventPlaceAcronym;
            $evento->ubicacion = $eventoEnvio->eventPlaceName;
            $arrayEventos[] = $evento;
        }

        $rastreo->eventos = $arrayEventos;

        $responseTrack->setRequest($this->service->__getLastRequest());
        $responseTrack->setResponse($this->service->__getLastResponse());
        $responseTrack->setTrack($rastreo);

        //busca track en db
      
        $encontrarTrack = TrackMensajeriaResponse::where('guia_mensajeria_id', $this->getGuiaMensajeria()->id)->get();

        if($encontrarTrack->count() > 0){
            $ultimoTrack = $encontrarTrack->last();
            $ultimoTrackResponse = $ultimoTrack['response'];
            $ultimoXmlResponse = new \DOMDocument();
            $ultimoXmlResponse->preserveWhiteSpace = FALSE;
            $ultimoXmlResponse->loadXML($ultimoTrackResponse);
            $ultimosEventos = $ultimoXmlResponse->getElementsByTagName('History')->length;
            
            if(count($responseTrack->getTrack()->eventos) == $ultimosEventos){
                $responseTrack->setActualiza(false);
            }
        }

        return $responseTrack;
    }
    
    public function verificarExcedente($response){
        $xmlResponse = new \DOMDocument();
        $xmlResponse->preserveWhiteSpace = FALSE;
        $xmlResponse->loadXML($this->getResponse());
        $codes = $xmlResponse->getElementsByTagName('statusSPA');
        $entregado = false;
        foreach ($codes as $code){
            if($code->nodeValue == 'CONFIRMADO') {
                $entregado = true;
                break;
            }
            
        }
        $tipoPaquete = $response->ExecuteQueryResult->trackingData->TrackingData->packageType;
        if(!$entregado && $tipoPaquete == "Paquete"){
            
            $excedente = GuiaMensajeria::select('guias_mensajerias.*',\DB::raw('guias_excedentes.id as guia_excedente_id'))
            ->leftjoin('guias_excedentes','guias_mensajerias.guia','=','guias_excedentes.guia')
            ->where('guias_mensajerias.guia', $this->getGuiaMensajeria()->guia)
            ->where('guias_mensajerias.status_entrega','!=',GuiaMensajeria::$status['entregada'])
            ->get()->last();
            
            $excedentePeso = 0;
            if(!$excedente->guia_excedente_id){
                
                $bitacoraCotizacion = BitacoraCotizacionMensajeria::findOrFail($excedente->bitacora_cotizacion_mensajeria_id);
                $pesoNormal = $response->ExecuteQueryResult->trackingData->TrackingData->dimensions->weight;
                $pesoVolumetrico = $response->ExecuteQueryResult->trackingData->TrackingData->dimensions->volumetricWeight;
                $pesoTrack = ($pesoNormal > $pesoVolumetrico ) ? $pesoNormal : $pesoVolumetrico;
                $tieneExcedente = false;
                if($pesoTrack > $bitacoraCotizacion->peso){
                    $excedentePeso = $pesoTrack - $bitacoraCotizacion->peso;
                    $tieneExcedente = true;
                }
                
                if($tieneExcedente){
                    $guiaExcendente = New GuiaExcedente();
                    $guiaExcendente->guia_mensajeria_id = $excedente->id;
                    $guiaExcendente->guia = $excedente->guia;
                    $guiaExcendente->excedente_peso = $excedentePeso;
                    $guiaExcendente->save();
                }
            }
        }
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

    /**
     * @return mixed
     */
    public function getGuiaMensajeria()
    {
        return $this->guia_mensajeria;
    }

}