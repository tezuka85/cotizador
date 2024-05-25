<?php

namespace App\ClaroEnvios\Mensajerias;


use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\GuiaExcedente\GuiaExcedente;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
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
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use stdClass;
use DHL\Datatype\GB\Piece;
use DHL\Entity\EA\KnownTrackingRequest as Tracking;


/**
 * Class MensajeriaMoova
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 */
class MensajeriaMoova extends MensajeriaMaster implements MensajeriaCotizable
{
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
        'PRODUCCION' => "https://api-prod.moova.io/b2b/",
        'TEST'       => "https://api-dev.moova.io/b2b/",
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
    protected $pedido;
    protected $costo_seguro;
    protected $costo_zona_extendida;
    protected $numero_externo;
    protected $id_configuracion;

    use AccesoConfiguracionMensajeria;

    /**
     *constructor.
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
            $this->pedido = $mensajeriaTO->getPedido();
            $this->costo_zona_extendida = $mensajeriaTO->getCostoZonaExtendida();
            $this->numero_externo = $mensajeriaTO->getNumeroExterno();
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();

            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());

            if($mensajeriaTO->getNegociacionId() == 1){
                $accesoComercioMensajeriaTO->setComercioId(1);
            }
            Log::info('Comercio: '.$mensajeriaTO->getComercio().', '.'Negociacion: '.$mensajeriaTO->getNegociacionId());
            Log::info('Llaves comercio: '.$accesoComercioMensajeriaTO->getComercioId());
            $this->configurarAccesos($accesoComercioMensajeriaTO);

            if(isset($this->configuracion)) {
                if($location == 'produccion' || $location == 'release') {
                    $this->configuracion->put('location', $this->arrayUrl['PRODUCCION']);
                    $this->configuracion->put('mode', 'production');
                    $this->configuracion->put('endpoint', $this->arrayUrl['PRODUCCION']);
                    Log::info( $this->configuracion);

                }else{
                    if(Auth::user()->hasRole('superadministrador')){
                        $this->configuracion = $this->getTestKeys();
                    }

                    $this->configuracion->put('location', $location);
                    $this->configuracion->put('endpoint', $this->arrayUrl['TEST']);
                    Log::info( $this->configuracion);
                }

            }else {
                throw new \Exception("No cuenta con llaves de accceso a la mensajeria");
            }


            $this->location = (env('API_LOCATION') == 'test')?$this->configuracion->get('location'):env('API_LOCATION');

        }
    }

    public function rate($traerResponse = false)
    {

    }

    public function generarGuiaMensajeria(GuiaMensajeriaTO $guiaMensajeriaTO,$format = 'ZPL2')
    {
        Log::info('Inicia configuracion para request ');
        $bitacoraMensajeriaDestinoTO = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();

        if (($bitacoraMensajeriaDestinoTO instanceof BitacoraMensajeriaDestinoTO) && ($bitacoraMensajeriaOrigenTO instanceof BitacoraMensajeriaOrigenTO)
            && ($bitacoraCotizacionMensajeriaTO instanceof BitacoraCotizacionMensajeriaTO)) {

            $servicio = ServicioMensajeria::where('nombre',$bitacoraCotizacionMensajeriaTO->getTipoServicio())->firstOrFail();
            $pedido = $this->pedido ? "(".$this->pedido.")"." "  :'';
//            die(print_r($pedido));
            $data = [
                "scheduledDate"=>"",
                "type"=>$servicio->nombre,
                "flow"=>"automatic",
                "from"=>[
//                    "addressDescription"=>"Calle 23 N 342, Platanos",
                    "street"=>$bitacoraMensajeriaOrigenTO->getCalle(),
                    "number"=>$bitacoraMensajeriaOrigenTO->getNumero(),
                    "floor"=>"",
                    "apartment"=>"",
                    "city"=>$bitacoraMensajeriaOrigenTO->getMunicipio(),
                    "state"=>$bitacoraMensajeriaOrigenTO->getEstado(),
                    "postalCode"=>$bitacoraCotizacionMensajeriaTO->getCodigoPostalOrigen(),
                    "country"=>"MEX",
                    "address"=>$bitacoraMensajeriaOrigenTO->getDireccionCompuesta().' '.$bitacoraMensajeriaOrigenTO->getMunicipio(),
                    "instructions"=>$bitacoraMensajeriaOrigenTO->getReferencias(),
                    "contact"=>[
                        "firstName"=>$bitacoraMensajeriaOrigenTO->getNombre(),
                        "lastName"=>$bitacoraMensajeriaOrigenTO->getApellidos(),
                        "email"=>$bitacoraMensajeriaOrigenTO->getEmail(),
                        "phone"=>$bitacoraMensajeriaOrigenTO->getTelefono()
                    ],
                    "message"=>""
                ],
                "to"=>[
//                    "addressDescription"=>"Calle 23 N 342, Platanos",
                    "street"=>$bitacoraMensajeriaDestinoTO->getCalle(),
                    "number"=>$bitacoraMensajeriaDestinoTO->getNumero(),
                    "floor"=>"",
                    "apartment"=>"",
                    "city"=>$bitacoraMensajeriaDestinoTO->getMunicipio(),
                    "state"=>$bitacoraMensajeriaDestinoTO->getEstado(),
                    "postalCode"=>$bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino(),
                    "country"=>"MEX",
                    "address"=>$bitacoraMensajeriaDestinoTO->getDireccionCompuesta().' '.$bitacoraMensajeriaDestinoTO->getMunicipio(),
                    "instructions"=>$bitacoraMensajeriaDestinoTO->getReferencias(),
                    "contact"=>[
                        "firstName"=>$pedido." ".$bitacoraMensajeriaDestinoTO->getNombre(),
                        "lastName"=>$bitacoraMensajeriaDestinoTO->getApellidos(),
                        "email"=>$bitacoraMensajeriaDestinoTO->getEmail(),
                        "phone"=>$bitacoraMensajeriaDestinoTO->getTelefono()
                    ],
                    "message"=>""
                ],
                "internalCode"=>"",
                "description"=>"",
                "label"=>"",
                "extra"=>[],
                "settings"=>[],
                "returnSettings"=>[],
                "conf"=>[
                    "assurance"=>null //$bitacoraCotizacionMensajeriaTO->getSeguro()//preguntar si debe ir este dato aqui, no implementado aun por moova
                ],
                "items"=>[
                    [
                        "description"=>$guiaMensajeriaTO->getContenido(),
                        "reference_code"=>$this->pedido,
                        "weight"=>$this->peso,
                        "length"=>$this->largo,
                        "width"=>$this->ancho,
                        "height"=>$this->alto,
                        "price"=>$this->valor_paquete,
                        "currency"=>"MXN",
                        "quantity"=>1
                    ]
                ]
            ];
        }
        try {

            $options = [
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'Authorization' => $this->configuracion['secretKey']],
                'defaults' => ['verify' => false]
            ];
//
            $client = new Client($options);

            Log::info($data);
            $res = $client->request('POST', $this->configuracion['endpoint'] . 'shippings',
                [RequestOptions::JSON => $data, RequestOptions::QUERY => ['appId' => $this->configuracion['appId']]]);

            $response = $res->getBody()->getContents();
            Log::info($response);
            $this->setRequest(json_encode($data));
            $this->setResponse(json_encode($response));
            $this->setCodeResponse($res->getStatusCode());
            return $response;
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }
    }

    public function generarLabel($id)
    {
        Log::info('Inicia solicitud de url de pdf');

        try {

            $options = [
                'headers' => ['Accept' => 'application/json', 'Content-Type' => 'application/json', 'Authorization' => $this->configuracion['secretKey']],
                'defaults' => ['verify' => false]
            ];

            $client = new Client($options);

            $response = $client->request('GET', $this->configuracion['endpoint'] . 'shippings/' . $id . '/label',
                [RequestOptions::QUERY => ['appId' => $this->configuracion['appId']]])
                ->getBody()->getContents();

            return $response;
        }
        catch (GuzzleHttp\Exception\ClientException $e) {
            $response = $e->getResponse();
        }
    }

    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO){
        $locat = env('API_LOCATION','test');
        $delivery = json_decode($this->generarGuiaMensajeria($guiaMensajeriaTO));

        if(isset($delivery->status) && $delivery->status == "error"){
            throw new \Exception("Mensajería responde: " . $delivery->message);
        }
        if($delivery->id && ($delivery->addressErrors == null)){
            $getUrlPdf = json_decode($this->generarLabel($delivery->id));
        }else{
            throw new \Exception("Mensajería responde: " . json_encode($delivery->addressErrors));
        }
        $file = $this->generateFile($getUrlPdf->label);
        $tracking_id = $delivery->id;
        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath . ('/' . $tracking_id  . '_' . date('YmdHis') . '.pdf');
        file_put_contents($rutaArchivo, $file);
        $nombreArchivo = $tracking_id . '_' . date('YmdHis') . '.pdf';
        $linktrack = ($locat == 'produccion' || $locat == 'release')? 'https://prod.moova.io/external/': 'https://dev.moova.io/external/';
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file) : base64_encode($file);

        $array = [
            'guia' => $tracking_id,
            'imagen' => $dataFile,
            'extension' => 'pdf',
            'nombreArchivo' => $nombreArchivo,
            'ruta' => $rutaArchivo,
            'link_rastreo_entrega' => $linktrack . $tracking_id,
            'infoExtra' => [
                'codigo' => 'WA',
                'identificadorUnico'=> '',
                'fecha_hora'=> date('Y-m-d H:i:s'),
                'tracking_link' => $linktrack . $tracking_id
            ]
        ];
        $array['location'] = (env('API_LOCATION') == 'test') ? "Local-Test" : env('API_LOCATION');

        return $array;
    }

    private function generateFile($urlGuia){

        Log::info("GenerateFile:");
        $token = $this->configuracion->get('token');
        try{
            $options=[
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false
            ];

            Log::info("URL PDF:"); Log::info($urlGuia);

            $client = new Client();
            $response = $client->request('GET', $urlGuia,$options)->getBody()->getContents();

            Log::info("RESPONSE PDF:"); Log::info($response);

            }catch (\Exception $exception){
                Log::info("ERROR peticion PDF:");
                Log::info($exception->getMessage());

                throw new \Exception($exception->getMessage(),$exception->getCode());
            }

        return $response;

    }

    public function rastreoGuia(){}

    public function verificarExcedente($response){}


    public function configuracionCotizacion(){}

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


    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO){}


    /**
     * Recoleccion para varios paquetes sin mensajeria
     * @param MensajeriaRecoleccionTO $mensajeriaRecoleccionTO
     * @return stdClass
     * @throws \Exception
     */
    public function recoleccionMensajeria(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        Log::info('Recoleccion mensajeria: '.$mensajeriaRecoleccionTO->getmensajeria()->clave);
        $recoleccion = new stdClass();
        $recoleccion->status = 'Success';
        $recoleccion->mensaje = 'Confirmacion de exitosa';

        $arrayResponse = $this->configuracionRecoleccionMensajeria($mensajeriaRecoleccionTO);
        $response = json_decode(json_encode($arrayResponse['response']));
        Log::info('Response recoleccion:');
        Log::info(json_encode($response));

        $recoleccion->location = $this->location;

        if (isset($response->Response->Status) && $response->Response->Status->ActionStatus == 'Error') {
            $condition = $response->Response->Status->Condition;
            throw new \Exception($condition->ConditionData);
        }

        $recoleccion->pick_up = $response->ConfirmationNumber;
        $recoleccion->localizacion = $response->OriginSvcArea;
        $recoleccion->request = $arrayResponse['request'];
        $recoleccion->response = $arrayResponse['response']->asXML();

        //$recoleccion->response = $response->asXML();
        return $recoleccion;
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

    public function validarCampos(){}

    private function getTestKeys(){
        return collect([
            "appId" => "19f71e90-3cdf-11ec-9d26-83032bf5f9c8",
            "secretKey" => "a3f8c81c7cf3c884e9e83fe6f49ae9259e32ad0b",

        ]);
    }

    public function getTipoServicio(){}


}
