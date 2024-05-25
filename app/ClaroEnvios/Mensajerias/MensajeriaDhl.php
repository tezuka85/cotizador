<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\GuiaExcedente\GuiaExcedente;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Track\TrackingMensajeria;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponse;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponseTO;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Xml\Dhl;
use App\ClaroEnvios\ZPL\ZPL;
use App\Exceptions\ValidacionException;
use Carbon\Carbon;
use DHL\Datatype\AM\PieceType;
use DHL\Client\Web as WebserviceClient;
use DHL\Entity\AM\GetQuote;
use DHL\Entity\GB\ShipmentRequest;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use stdClass;
use DHL\Datatype\GB\Piece;
use DHL\Entity\EA\KnownTrackingRequest as Tracking;
use DHL\Datatype\GB\SpecialService;
use voku\helper\UTF8;


/**
 * Class MensajeriaDhl
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 */
class MensajeriaDhl extends MensajeriaMaster implements MensajeriaCotizable
{
    public static $arrayServicioDescripcion = [
        'ECONOMY SELECT DOMESTIC'=>'Economico',
        'EXPRESS DOMESTIC'=>'Dia Siguiente'
    ];
    public static $arrayGlobalProductCode = [
        'ECONOMY SELECT DOMESTIC'=>'G',
        'EXPRESS DOMESTIC'=>'N'
    ];
    public static $arrayLocalProductCode = [
        'ECONOMY SELECT DOMESTIC'=>'G',
        'EXPRESS DOMESTIC'=>'N'
    ];
    public static $arrayServiciosPermitidos = [
        'ECONOMY SELECT DOMESTIC',
        'EXPRESS DOMESTIC'
    ];
    protected $porcentaje;
    protected $peso;
    protected $largo;
    protected $ancho;
    protected $alto;
    protected $dias_embarque;
    protected $codigo_postal_origen;
    protected $codigo_postal_destino;
    protected $siglas_codigo_postal_destino;
    protected $siglas_codigo_postal_origen;
    protected $guia_mensajeria;
    protected $costo;
    protected $porcentaje_seguro;
    protected $valor_paquete;
    private $arrayUrl = [
        'PRODUCCION'=>"https://xmlpi-ea.dhl.com/XMLShippingServlet?isUTF8Support=true",
        //'TEST'=>      "https://xmlpi-ea.dhl.com/XMLShippingServlet?isUTF8Support=true",
        'TEST'=>"https://xmlpitest-ea.dhl.com/XMLShippingServlet?isUTF8Support=true"
    ];

    protected $formato_guia_impresion;
    protected $extension_guia_impresion;
    protected $fecha_liberacion;
    protected $request;
    protected $response;
    protected $negociacion_id;
    protected $porcentaje_calculado;
    protected $costo_calculado;
    protected $costo_adicional;
    protected $location;
    protected $seguro;
    protected $code_response;
    protected $costo_seguro;
    protected $costo_zona_extendida;
    protected $numero_externo;
    protected $peso_calculado;
    protected $comercio_id;
    protected $negociacion;
    protected $piezas;
    protected $id;
    protected $id_configuracion;

    protected $paquetes;//variables para soportar paquetes multiguia
    protected $paquetes_detalle;

    use AccesoConfiguracionMensajeria;

    /**
     * MensajeriaDhl constructor.
     */
    public function __construct($mensajeriaTO = false)
    {
        $location = env('API_LOCATION','test');
        if ($mensajeriaTO instanceof MensajeriaTO) {
            $this->id = $mensajeriaTO->getId();
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
            $this->costo_adicional = $mensajeriaTO->getCostoAdicional();
            $this->seguro = $mensajeriaTO->getSeguro();
            $this->costo_zona_extendida = $mensajeriaTO->getCostoZonaExtendida();
            $this->numero_externo = $mensajeriaTO->getPedido();
            $this->peso_calculado = $mensajeriaTO->getPesoCalculado();
            $this->comercio_id = $mensajeriaTO->getComercio();
            $this->negociacion = $mensajeriaTO->getNegociacion();
            $this->piezas = $mensajeriaTO->getPiezas();
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();
            //variables para soportar paquetes multiguia
            $this->paquetes = $mensajeriaTO->getPaquetes();
            $this->paquetes_detalle = $mensajeriaTO->getPaquetesDetalle();

            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());

            if($mensajeriaTO->getNegociacionId() == 1){
                $accesoComercioMensajeriaTO->setComercioId(1);
            }
            Log::info('Comercio: '.$mensajeriaTO->getComercio().', '.'Negociacion: '.$mensajeriaTO->getNegociacionId());
            Log::info('Llaves comercio: '.$accesoComercioMensajeriaTO->getComercioId());
            $this->configurarAccesos($accesoComercioMensajeriaTO);
           // die(print_r($mensajeriaTO));
            if(isset($this->configuracion)) {
                if($location == 'produccion' || $location == 'release') {
                    $this->configuracion->put('location', $this->arrayUrl['PRODUCCION']);
                    $this->configuracion->put('mode', 'production');
//                    Log::info( $this->configuracion);

                }else{
                    if(Auth::user()->hasRole('superadministrador')){
                        $this->configuracion = $this->getTestKeys();
                    }else{
                        $this->configuracion->put('location', $this->arrayUrl['TEST']);
                        $this->configuracion->put('endpoint', $this->arrayUrl['TEST']);
                        $this->configuracion->put('mode', 'staging');
                    }
//                    Log::info( $this->configuracion);
                }
                if(!$mensajeriaTO->getTabulador()){
                    Log::info( $this->configuracion);
                }

            }else{
                throw new \Exception("No cuenta con llaves de accceso a la mensajeria DHL");
            }

            Log::info('Ambiente .env: '.$location);
            $this->location = (env('API_LOCATION') == 'test')?$this->configuracion->get('location'):env('API_LOCATION');

        }
    }

    public function rate($traerResponse = false)
    {
        $tarificador = new stdClass();
        $response = $this->ratePeticion();

        if($response['code_response'] != 200){
            $tarificador->success = false;
            $tarificador->message = $response['response'];
            return $tarificador;
        }

        $getQuoteResponse = $response['xmlArr']->GetQuoteResponse ?? new stdClass();
        if (property_exists($response['xmlArr'], 'GetQuoteResponse')) {
            Log::info("RESPONSE: ".json_encode($getQuoteResponse));
        }

        if (property_exists($getQuoteResponse, 'BkgDetails')) {
            Log::info("Response BkgDetails");

            $tarificador->success = true;
            $tarificador->message = Response::$messages['successfulSearch'];
            if ($traerResponse) {
                $tarificador->request = $response['request'];
                $tarificador->response = $response['response'];
                $tarificador->code_response = $this->getCodeResponse();
//                Log::error(json_encode($response['response']));
            }
            
            foreach ($getQuoteResponse->BkgDetails->QtdShp as $data) {
                if (!property_exists($data, 'ProductShortName')) {
                    Log::error("No property ProductShortName");
                    Log::error(json_encode($data));
                    throw new \Exception("No peoperty ProductShortName");
                }

                if (in_array($data->ProductShortName, self::$arrayServiciosPermitidos)) {
                    if (!property_exists($tarificador, 'servicios')) {
                        $tarificador->servicios = new stdClass();
                    }
                    Log::info("Servicio: ".$data->ProductShortName);
                    $costo = round($data->ShippingCharge, 2);
                    $costoAdicional = 0;
                    $costoSeguro = 0;

                    //die(print_r($this->porcentaje));

                    if ($this->costo != 0) {
                        $costoAdicional = $this->costo;
                        Log::info(' Costo margen: '.$this->costo);
                    } elseif ($this->porcentaje != 0) {
                        $costoAdicional = round($costo * ($this->porcentaje / 100), 2);
                        Log::info(' Porcentaje margen: '.$this->porcentaje);
                    }

                    Log::info(' Costo guia mensajeria DHL: '.$costo);
                    if (in_array($this->id_configuracion, array_merge(ConfiguracionComercio::$comerciosZonas,[2,9]))) {
                        Log::info(' Calculo zonas: ');

                        $costoGuia = $costo;
                        $costoTotalCalculado = round(($costoGuia /(1-($this->porcentaje/100))) , 2);
                        Log::info(' Costo Total zonas: ' . $costoTotalCalculado);

                    }else {
                        Log::info(' Calculo default: ');
                        $costoSeguro = $this->seguro ? round($this->valor_paquete * ($this->porcentaje_seguro / 100), 2) : 0;
                        Log::info(' Costo adicional calculado: '.$costoAdicional);
                        Log::info(' Costo Seguro ' . $costoSeguro);
                        $costoTotalCalculado = round($costo + $costoAdicional + $costoSeguro, 2);
                        Log::info(' Costo Total: ' . $costoTotalCalculado);
                    }

                    $servicioMensajeria =  $this->obtenerServicioMensajeria('DHL',$data->ProductShortName);
                    $fechaEntrega = new Carbon($data->DeliveryDate);
//                    die("<pre>".print_r($fechaEntrega));
                   
                    $servicio = $this->responseService($costo,$costo,$costoTotalCalculado,  $servicioMensajeria, $fechaEntrega,$costoSeguro,false ,$this->paquetes);


                    $tarificador->servicios->{$data->ProductShortName} = $servicio;
                    $tarificador->location  = $this->location;
                }
            }
           // die("<pre>".print_r($tarificador));
            if (property_exists($tarificador, 'servicios')) {
                return $tarificador;
            }
        }
        $tarificador->success = false;
        $tarificador->codigo = $response['xmlArr']->Response->Status->Condition->ConditionCode
            ?? ($getQuoteResponse->ConditionCode ?? '0');
        $tarificador->message = $response['xmlArr']->Response->Status->Condition->ConditionData
            ?? ($getQuoteResponse->ConditionData ?? 'Verificar la conexion');
        Log::info('Mensaje api mensajeria: '.$tarificador->message);
        return $tarificador;
    }

    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO){

    }

    public function verificarExcedente($response){
        $xmlResponse = new \DOMDocument();
        $xmlResponse->preserveWhiteSpace = FALSE;
        $xmlResponse->loadXML($this->getResponse());
        $codes = $xmlResponse->getElementsByTagName('EventCode');
        $entregado = false;
        foreach ($codes as $code){
            if($code->nodeValue == 'OK') {
                $entregado = true;
                break;
            }

        }

        if(!$entregado){

            //buscar si existe un excedente
            $excedente = GuiaMensajeria::select('guias_mensajerias.*',DB::raw('guias_excedentes.id as guia_excedente_id'))
                ->leftjoin('guias_excedentes','guias_mensajerias.guia','=','guias_excedentes.guia')
                ->where('guias_mensajerias.guia', $this->getGuiaMensajeria()->guia)
                ->where('guias_mensajerias.status_entrega','!=',GuiaMensajeria::$status['entregada'])
                ->get()->last();
            $excedentePeso = 0;

            if(!$excedente->guia_excedente_id){

                $bitacoraCotizacion = BitacoraCotizacionMensajeria::findOrFail($excedente->bitacora_cotizacion_mensajeria_id);
                $pesoTrack = $response->ShipmentInfo->Weight;

                if($response->ShipmentInfo->WeightUnit != 'KG' || $response->ShipmentInfo->WeightUnit != 'K'){
                    $pesoTrack = conversionKilogramos($response->ShipmentInfo->WeightUnit, $response->ShipmentInfo->Weight);
                }

//                $pesoTrack = 20;
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
                //  die("<pre>".var_dump($tieneExcedente));
            }
        }

    }

    private function ratePeticion()
    {
        try{
            $numPaquetes = ($this->paquetes!=null) ? $this->paquetes : 0;
            $paquetesDetail = ($this->paquetes_detalle!=null) ? $this->paquetes_detalle : null;
            $data = collect([
                'codigo_postal_origen'=>$this->codigo_postal_origen,
                'fecha_liberacion'=>$this->fecha_liberacion,
                'alto'=>$this->alto,
                'ancho'=>$this->ancho,
                'largo'=>$this->largo,
                'peso'=>$this->peso_calculado,
                'codigo_postal_destino'=>$this->codigo_postal_destino,
                'seguro'=>$this->seguro,
                'valor_paquete'=>$this->valor_paquete,
                'paquetes'=>$numPaquetes,
                'paquetes_detail'=>$paquetesDetail
            ]);

            $xml = Dhl::cotizacion($this->configuracion,$data);
            $headers = [
                'headers' => ['Accept' => 'application/json', 'Content-Type'=>'application/json'],
                'defaults' => ['verify' => false,'port'=>443],
                'timeout'  => 10,
            ];
            $request = new Request(
                'POST',
                $this->configuracion->get('location'),
                ['Content-Type' => 'text/xml; charset=UTF8'],
                $xml
            );

            //Log::info($xml);
            $client = new Client($headers);
            $response = $client->post($this->configuracion->get('location'), ['body' => $xml ]);
            $statusResponse = $response->getStatusCode();
            $content = $response->getBody()->getContents();
    //        die(print_r($content));

            $this->setResponse($content);
            $this->setCodeResponse($statusResponse);

            //convert xml string into an object
            $xml_convert = simplexml_load_string($content);
            //convert into json
            $json = json_encode($xml_convert);
            //convert into associative array
            $xmlArr = json_decode($json);
            return [
                'request'=>$request->getBody()->getContents(),
                'response'=>$content,
                'code_response'=>$statusResponse,
                'xmlArr'=>$xmlArr
            ];
        }catch(\Exception $error){
            return [
                'request'=>$request->getBody()->getContents(),
                'response'=>$error->getMessage(),
                'code_response'=>504,
                'xmlArr'=>null
            ];
        }
    }

    public function configuracionCotizacion()
    {
        $requestData = new GetQuote();
        $fechaHeader = gmdate(DATE_ATOM);
        // Set values of the request
        $requestData->MessageTime = $fechaHeader;
        $requestData->MessageReference = '1234567890123456789012345678901';
        $requestData->SiteID = $this->configuracion->get("siteId");
        $requestData->Password = $this->configuracion->get("password");
//        $requestData->SoftwareName = "API_T1Envios";
//        $requestData->SoftwareVersion = "6";

        //FROM
        $requestData->From->CountryCode = 'MX';
        $requestData->From->Postalcode = $this->codigo_postal_origen;

        //BKGDETAILS
        $requestData->BkgDetails->PaymentCountryCode = 'MX';
        //$requestData->BkgDetails->Date = date('Y-m-d');
        $requestData->BkgDetails->Date = $this->fecha_liberacion->format('Y-m-d');
        $requestData->BkgDetails->ReadyTime = $this->fecha_liberacion->format('\P\TH\Hi\Ms\S');
        //$requestData->BkgDetails->ReadyTime = 'PT10H21M';
        $requestData->BkgDetails->ReadyTimeGMTOffset = $this->fecha_liberacion->format('P');
        $requestData->BkgDetails->DimensionUnit = 'CM';
        $requestData->BkgDetails->WeightUnit = 'KG';

        //Cantidad Productos

        //Quitar para documento
        $piece = new PieceType();
        $piece->PieceID = 1;
        $piece->Height = $this->alto;
        $piece->Depth = $this->ancho;
        $piece->Width = $this->largo;
        $piece->Weight = $this->peso;
        $requestData->BkgDetails->addPiece($piece);

        $requestData->BkgDetails->PaymentAccountNumber = $this->configuracion->get("shipperAccountNumber");
        $requestData->BkgDetails->IsDutiable      = 'N';
        $requestData->BkgDetails->NetworkTypeCode = 'AL';
        //$requestData->BkgDetails->InsuredValue    = '1000.00';
        //$requestData->BkgDetails->InsuredCurrency = 'MXN';

        //TO
        $requestData->To->CountryCode = 'MX';
        $requestData->To->Postalcode  = $this->codigo_postal_destino;

        //Dutiable
        //$requestData->Dutiable->DeclaredValue    = '100.00';
        //$requestData->Dutiable->DeclaredCurrency = 'MXN';

        return $requestData;
    }

    /**
     * @return mixed
     */
    public function getGuiaMensajeria()
    {
        return $this->guia_mensajeria;
    }

    /**
     * @param mixed $guiaMensajeria
     */
    public function setGuiaMensajeria(GuiaMensajeria $guiaMensajeria): void
    {
        $this->guia_mensajeria = $guiaMensajeria;
    }

    private function callWebServices($xml, $url = null)
    {
        $url = is_null($url) ? $this->configuracion->get('location') : $url;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($ch, CURLOPT_BINARYTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);


        $resultado = curl_exec($ch);
        curl_close($ch);
        return (new \SimpleXMLElement($resultado));
    }

    public function getTipoServicio()
    {
        return self::$arrayServiciosPermitidos[0];
    }

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO)
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

    public function validarCampos(){

        $rules = [
            "siteId" => 'required',
            "password" => 'required',
            "shipperAccountNumber" => 'required',
            "billingAccountNumber" => 'required',
            "dutyAccountNumber" => 'required',
            "shipperId" => 'required',
            "registeredAccount" => 'required',
        ];

        return $rules;
    }

    private function getTestKeys(){
        return collect([
            "siteId" => "xmlSANBORNH",
            "password" => "ke3tUXq8RJ",
            "shipperAccountNumber" => '980223453',
            "billingAccountNumber" => '980223453',
            "dutyAccountNumber" => '980223453',
            "shipperId" => '980223453',
            "registeredAccount" => '980223453',//988178610
            "location" => "https://xmlpi-ea.dhl.com/XMLShippingServlet",
            "mode_generar_guia" => 'staging',
            "mode" => 'staging',
            "endpoint" => "https://xmlpi-ea.dhl.com/XMLShippingServlet?isUTF8Support=true"//esta se usa para indicar la url , wraper ya tiene por default la url de produccion
        ]);
    }

}
