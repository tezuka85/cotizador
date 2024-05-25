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
use App\ClaroEnvios\ZPL\ZPL;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use SKAgarwal\GoogleApi\PlacesApi;
use \Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaNoventaNueveMin
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 * @author Roberto Martinez
 */

class MensajeriaNoventaNueveMin extends MensajeriaMaster implements MensajeriaCotizable
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
    protected $costo_zona_extendida;
    protected $numero_externo;
    protected $id_configuracion;
    protected $ID;


    private $arrayLabelUrl = [
        'PRODUCCION'=>"https://delivery.99minutos.com/api/v1/",
        'TEST'=>"https://sandbox.99minutos.com/api/v1/"

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
            $this->ID = $mensajeriaTO->getId();
            $this->custom = "";
            $this->costo_zona_extendida = $mensajeriaTO->getCostoZonaExtendida();
            $this->numero_externo = $mensajeriaTO->getNumeroExterno();
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();

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

            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                $this->url_tracking = "https://tracking.99minutos.com/search/";
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->url_tracking = "https://tracking.99minutos.com/search/";
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
        $destino = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $origen = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $tiendaOrigen = TiendaUber::where('id_tienda',$this->tiendaId)->first();
        $array=[];
        
        $delivery = $this->createDelivery($destino, $guiaMensajeriaTO, $origen, $cotizacion);
        //Intenta crear Archivo
        $file =  $this->generateFile($delivery->message);
        $file = json_decode($file);
        $file = substr($file->pdf, 2, -1);
        $file = base64_decode($file);

        $trackingNumber = $delivery->message['0']->reason->counter;
        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath.('/'.$trackingNumber.'_'.date('YmdHis').'.'.$this->extension_guia_impresion);
        file_put_contents($rutaArchivo, $file);

        $nombreArchivo = $trackingNumber.'_'.date('YmdHis').'.pdf';
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file) : base64_encode($file);

        $array['guia']=$trackingNumber;
        $array['imagen']=$dataFile;
        $array['extension']="pdf";
        $array['nombreArchivo']=$nombreArchivo;
        $array['ruta']=$rutaArchivo;
        $array['link_rastreo_entrega'] =  env('TRACKING_LINK_T1ENVIOS')."".$trackingNumber;
        $array['location']=(env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION');
        $array['infoExtra']=[
            'codigo'=>'1',
            'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
            'identificadorUnico'=>'',
            'tracking_link' =>env('TRACKING_LINK_T1ENVIOS')."".$trackingNumber
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
            if($this->configuracion->count() >=1){
                $token = $data['apikey'];
                if($token) {
                    $options = [
                        "json" => $data,
                        'connect_timeout' => 90,
                        'http_errors' => true,
                        'verify' => false,
                        'headers' => [
                            'Content-Type' => 'application/json'
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

                    throw new \Exception("No cuenta con credenciales de mensajeria, apikey no encontrado");

                }
            }else{
                throw new \Exception("No cuenta con credenciales de mensajeria");
            }


        }catch (\Exception $exception){

            Log::error($exception->getMessage());


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
    public function createDelivery(BitacoraMensajeriaDestinoTO $destino, GuiaMensajeriaTO $guiaMensajeriaTO, $origen, $cotizacion)
    {
        $now = Carbon::now()->toDateString();
      
        Log::info("Entra en createDelivery");
        // $valorPaque = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO()->getValorPaquete();
        $tipoServicio = "nextDay";

        if($cotizacion->getTipoServicio() == "SAMEDAY"){
            $tipoServicio = "sameDay";
        }
        
        $usuario = Auth::user()->id;
        $secondaryC = "";
        Log::info("ID Usuario:". $usuario);
        $pedido = $this->pedido ? "(".$this->pedido.")"." "  :'';
        $data =
        [
            "apikey"=> $this->configuracion->get('apikey'),
            "deliveryType"=> $tipoServicio,
            "packageSize"=> "m",
            "notes"=> $pedido." ".$destino->getReferencias(),
            "cahsOnDelivery"=> false,
            "amountCash"=> ($this->valor_paquete) ? $this->valor_paquete : 0,
            "SecurePackage"=> false,
            "amountSecure"=> 0,
            "receivedId"=> "",
            "origin"=> [
              "sender"=> $origen->getNombre() ." ".$origen->getApellidos(),
              "nameSender"=> $origen->getNombre(),
              "lastNameSender"=> $origen->getApellidos(),
              "emailSender"=> $origen->getEmail(),
              "phoneSender"=> $origen->getTelefono(),
              "addressOrigin"=> $origen->getCalle()." ".$origen->getNumero()." ,". $this->codigo_postal_origen,
              "numberOrigin"=> $origen->getNumero(),
              "codePostalOrigin"=> $this->codigo_postal_origen,
              "country"=> "MEX"
            ],
            "destination"=> [
              "receiver"=> $destino->getNombre() ." ".$destino->getApellidos(),
              "nameReceiver"=> $destino->getNombre(),
              "lastNameReceiver"=> $destino->getApellidos(),
              "emailReceiver"=> $destino->getEmail(),
              "phoneReceiver"=> $destino->getTelefono(),
              "addressDestination"=> $this->limitar_cadena($destino->getCalle(), 80, "") ." ".$destino->getNumero().", ".$destino->getColonia()." ".$this->codigo_postal_destino,
              "numberDestination"=> $destino->getNumero(),
              "codePostalDestination"=> $this->codigo_postal_destino,
              "country"=> "MEX"
            ]
        ];

        $this->setRequest(json_encode($data));
        $response =  $this->makeRequest($data,"autorization/order");
        Log::info("Response 99Min");
        Log::info(json_encode($response));

        $res= $response->message['0'];

        if ($res->message != 'Creado') {
            Log::info("ERROR peticion 99min:".$res->message);

            throw new \Exception($res->message);
        }
        Log::info("Orden creada");
        return $response;
    }


    private function generateFile($data){

        try{

            $counter = $data['0']->reason->counter;

            $token = $this->configuracion->get('apikey');

            $request = [

                "counter"=>[
                $counter
                ],
                "base64"=> true,
                "size"=> "zebra"

            ];

            $options=[
                "json"=>$request,
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers'  => [
                    'Content-Type'=>'application/json',
                    'Authorization'=>'Bearer '.$token
                ]
            ];

            Log::info("GUIA ZPL:"); Log::info($counter);


            $client = new Client();
            $response = $client->request('POST', $this->endpointLabel."guide/order",$options)->getBody()->getContents();
            
            Log::info("RESPONSE ZPL");

            if($response){
                return $response;
            }
            else{

                Log::info("ERROR peticion PDF 99min:");

                throw new \Exception("No hay respuesta de PDF 99min");
            }

        }catch (\Exception $exception){
            Log::info("ERROR peticion ZPL:");
            Log::info($exception->getMessage());

            throw new \Exception($exception->getMessage(),$exception->getCode());
        }

    }

    public function getTipoServicio(){}

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO){}

    public function validarCampos(){}

    public function verificarExcedente($response){}

    public function rate($traerResponse = false){}

    public function getCodeResponse()
    {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void
    {
        $this->code_response = $codeResponse;
    }


    public function limitar_cadena($cadena, $limite, $sufijo){
        if(strlen($cadena) > $limite){
            return substr($cadena, 0, $limite) . $sufijo;
        }

        return $cadena;
    }

    public function rastreoGuia(){}

}
