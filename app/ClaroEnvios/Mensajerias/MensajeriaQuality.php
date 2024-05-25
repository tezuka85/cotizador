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
 * Class MensajeriaQuality
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 * @author Roberto Martinez
 */
class MensajeriaQuality extends MensajeriaMaster implements MensajeriaCotizable
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
    private $url_tracking;

    private $keys;
    private $tiendaId;
    private $code_response;

    private $comercioNegociacionID;
    protected $negociacion;
    protected $costo_seguro;
    protected $id_configuracion;

    private $arrayLabelUrl = [
        'PRODUCCION'=>"http://clientes.qualitypost.com.mx//delivery_mobile/ws_peticiones/ot_request_filelabel.php",
        'TEST'=>"http://clientes.qualitypost.com.mx//delivery_mobile/ws_peticiones/ot_request_filelabel.php"

    ];

    private $arrayLoginUrl = [
        // 'PRODUCCION'=>"https://api.platform.cargamos.com/v1/credentials",
        // 'TEST'=>"https://api.unstable.cargamos.com/v1/credentials"

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
            $this->pedido = $mensajeriaTO->getPedido().date('dmY');
            $this->tipo = $mensajeriaTO->getTipo();
            $this->tiendaId = $mensajeriaTO->getTiendaId();
            $this->comercioNegociacionID = Auth::user()->comercio_id;
            $this->negociacion = $mensajeriaTO->getNegociacion();
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();
            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());
            

            if($mensajeriaTO->getNegociacionId() == 1){
                $accesoComercioMensajeriaTO->setComercioId(1);
                $this->comercioNegociacionID = 1;
            }
          //  die(print_r($accesoComercioMensajeriaTO));
            Log::info('Comercio: '.$mensajeriaTO->getComercio().', '.'Negociacion: '.$mensajeriaTO->getNegociacionId());
            Log::info('Llaves comercio: '.$this->comercioNegociacionID);
            $this->configurarAccesos($accesoComercioMensajeriaTO);
            
            if(!$this->configuracion){
                $this->configuracion = collect();
            }
            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                //$this->endpointLogin = $this->arrayLoginUrl['PRODUCCION'];
                //$this->url_tracking = "https://tracking.cargamos.com/order-detail/";
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                //$this->endpointLogin = $this->arrayLoginUrl['TEST'];
                //$this->url_tracking = "https://tracking.cargamos.com/order-detail/";
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

            
            $array = [];
            // $placeApiDestino = $this->searchPlaceID($destino);

            //Busca si hay tiendas disponibles
            // $this->findStore($placeApiDestino);
            // $estimate = $this->createEstimate($tiendaOrigen, $placeApiDestino, $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId(),$this->pedido);
           
            $delivery = $this->createDelivery($destino, $guiaMensajeriaTO,$origen);
           
            if($guiaMensajeriaTO->getTipoDocumento() != 'zpl'){
                //Intenta crear ZPL
                $file = $this->generateFileZPL($delivery->zpl);
            }else{
                //die(print_r($guiaMensajeriaTO->getTipoDocumento()));
                $zpl = $delivery->zpl;
                if($guiaMensajeriaTO->getCodificacion() == 'utf8'){
                    $zpl =  base64_decode($delivery->zpl);
                }
               
                $file = ["success"=>true, "data"=>$zpl];
            }
           
            $tracking_id =$this->pedido;
            $tmpPath = sys_get_temp_dir();
            $rutaArchivo = $tmpPath . ('/' . $tracking_id  . '_' . date('YmdHis') . '.' . $this->extension_guia_impresion);
            file_put_contents($rutaArchivo, $file['data']);
            
            $nombreArchivo = $tracking_id . '_' . date('YmdHis') . '.pdf';
            $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file['data']) : base64_encode($file['data']);

            $array['guia'] = $tracking_id;
            $array['imagen'] = $dataFile;
            $array['extension'] = "pdf";
            $array['nombreArchivo'] = $nombreArchivo;
            $array['ruta'] = $rutaArchivo;
            $array['link_rastreo_entrega'] = env('TRACKING_LINK_T1ENVIOS')."".$tracking_id;
            // $array['link_rastreo_entrega'] = $this->url_tracking."".$tracking_id;
            $array['location'] = (env('API_LOCATION') == 'test') ? $this->endpointLabel : env('API_LOCATION');
            $array['infoExtra']=[
                'codigo'=>'100',
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
        Log::info('Entra a findStore');
        $placeId = $placeApiDestino->get('place_id');
        $url = "stores?place_id=".$placeId."&place_provider=google_places";
//        $url = "stores?place_id=ChIJgcRS0EHw0YURr8eHDRZUn_0&place_provider=google_places";

        $response =  $this->makeRequest([],$url,"GET");
        Log::info(json_encode($response));

        if(property_exists($response,'stores')){
            if(count($response->stores)== 0){
                $message = Response::$messages['noCoverage'];
                throw new \Exception($message);
            }
        }

        return $response;

    }

    private function createEstimate(TiendaUber $tiendaUberOrigen,Collection $placeApiDestino,$bitacoraCotizacionId){

        try{
            Log::info('Entra en createEstimate ');
            Log::info("Client_id: ".$this->configuracion->get('client_id'));

            $placeId = $placeApiDestino->get('place_id');
            $now = Carbon::now();
            $segundos = $now->addMinutes(15)->getTimestamp();
            $pickupTimes = $segundos * 1000;
            //        $date2 = date("d M Y H:i",$pickupTimes);
            $data = [
                "pickup"=> [
                    "store_id"=> "$tiendaUberOrigen->uuid_uber"
                ],
                "dropoff_address"=> [
                    "place"=>[
                        "id"=> $placeId,
                        "provider"=> "google_places"
                    ]
                ],
                "pickup_times"=> [$pickupTimes]
            ];

            $cotizacion = BitacoraCotizacionMensajeria::find($bitacoraCotizacionId);
            $response =  $this->makeRequest($data,'estimates','POST',$cotizacion->id);
            $this->guardaCotizacionResponse($bitacoraCotizacionId);
            $costoGuia = ($response->estimates[0]->delivery_fee->total)/100;

            $costoAdicional = 0;

            if ($cotizacion->costo_convenio != 0) {
                $costoAdicional = $cotizacion->costo_convenio;
            }elseif ($cotizacion->porcentaje != 0){
                $costoAdicional = round($costoGuia*($cotizacion->porcentaje/100), 2);
            }
            $costoSeguro = $cotizacion->seguro?round($cotizacion->valor_paquete*($cotizacion->porcentaje_seguro/100), 4):0;

            Log::info(' Costo Serguro '.$costoSeguro);
            Log::info(' Costo Adicional '.$costoAdicional);

            $costoTotalCalaculado = round($costoGuia + $costoAdicional + $costoSeguro, 4);
            $cotizacion->costo = $costoGuia;
            $cotizacion->costo_porcentaje = $costoTotalCalaculado;
            $cotizacion->updated_at = Carbon::now();
            $cotizacion->updated_usuario_id = auth()->user()->id;
            $cotizacion->token = $response->estimate_id;
            $cotizacion->save();
            Log::info("Estimate Id: ".$response->estimate_id);

            return $response;

        }catch (\Exception $exception){
            $error = $this->parseError( $exception->getMessage());
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
                $this->guardaCotizacionResponse($bitacoraCotizacionId);

            throw new \Exception($error->get('message'),$exception->getCode());
        }

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
        $options = [
            "json" => $data,
            'connect_timeout' => 90,
            'http_errors' => true,
            'verify' => false,
            'headers' => [
                'Content-Type' => 'application/json',
                'Optimize-Response-Time' => 'false'
            ]
        ];

        try{

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
                return json_decode($content);


        }catch (\GuzzleHttp\Exception\ClientException $exception){
            $error = $exception->getResponse()->getBody()->getContents();
            Log::error("Error makeRequest: ".$exception->getMessage().': '.$exception->getFile().' '.$exception->getLine());
            Log::error($exception->getResponse()->getBody()->getContents());

            $error = json_decode($error);
            $e = json_encode($error->errors["0"]);
            Log::error($e);
            throw new \Exception($e, $exception->getCode());
           
        }
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


    // consulta precios de envios
    public function rate($traerResponse = false){}

    private function getToken($grantType,$token = null){
        try{
            Log::info("user: ".$this->configuracion->get('username'));
            Log::info("pass: ".$this->configuracion->get('password'));
          
            $credentials = base64_encode($this->configuracion->get('username').':'.$this->configuracion->get('password'));
    
            Log::info("Genera nuevo token");
            Log::info("Endpoint login: ". $this->endpointLogin);
            Log::info("Credentials: ". $credentials);
             
            $curl = curl_init();

            curl_setopt_array($curl, array(
            CURLOPT_URL => $this->endpointLogin,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_ENCODING => '',
            CURLOPT_MAXREDIRS => 10,
            CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
            CURLOPT_CUSTOMREQUEST => 'GET',
            CURLOPT_HTTPHEADER => array(
                'Authorization: Basic '.$credentials
            ),
            ));

            $response = curl_exec($curl);
            $httpcode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);

            Log::info("RESPONSE TOKEN:");
            Log::info($response);

            if ($httpcode == 400) {
                throw new \Exception($response);
            }
            $result = json_decode($response);
            return $result->result->credentials->token;

        }catch (\Exception $exception){
            Log::error("ERROR getToken:");
            Log::error($exception->getMessage());
            throw new \Exception($exception->getMessage(),$exception->getCode());
        }
    }


    private function parseError($responseError){
       $json = $responseError;
      
        $responseQual = strpos($responseError,'response');

        if($responseQual){
            $json = substr($responseError,$responseQual +10);
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
            Log::info("Entra en createDelivery");
            $cliente = $this->configuracion->get('cliente');
            $token = $this->configuracion->get('token');

            if (!$cliente || !$token) {
                Log::error("No cuenta con credenciales de cliente o token mensajeria");
                throw new \Exception("No cuenta con credenciales de cliente o token mensajeria",404);
            }

            $data = [
                "Envio"=> [
                    "Cliente" => $cliente,
                    "Token" => $token,
                    "Pedido" => [
                        "ot"=> $this->pedido,
                        "remitente_nombre" => "{$this->clean($origen->getNombre())} {$this->clean($origen->getApellidos())}",
                        "remitente_telefono" => $origen->getTelefono(),
                        "remitente_calle" => $this->clean($origen->getDireccionCompuesta()),
                        "remitente_colonia" => $this->clean($origen->getColonia()),
                        "remitente_municipio" => $this->clean($origen->getMunicipio()),
                        "remitente_referencia" => $this->clean($origen->getReferencias()),
                        "remitente_cp" => $this->codigo_postal_origen,
                        "remitente_estado" => $this->clean($origen->getEstado()),
                        "nombre" =>  $this->clean($destino->getNombre()),
                        "telefono" => $destino->getTelefono(),
                        "calle" => $this->clean($destino->getCalle()),
                        "num_ext" => $destino->getNumero(),
                        "num_int" => "",
                        "colonia" => $this->clean($destino->getColonia()),
                        "municipio" => $this->clean($destino->getMunicipio()),
                        "pedido" => $this->clean($guiaMensajeriaTO->getContenido()),
                        "dimension" => "{$this->largo}x{$this->alto}x{$this->ancho}",
                        "peso" => "{$this->peso}",
                        "precio" => "{$this->valor_paquete}",
                        "email" => $destino->getEmail(),
                        "estado" => $this->clean($destino->getEstado()),
                        "cp" => $this->codigo_postal_destino,
                        "comentarios" => $this->clean($destino->getReferencias()),
                        "tipo" => "SAD"
                    ]
                ]
            ];
           
            Log::info("JSON QUALITY DELIVERY: ");
            Log::info(json_encode($data));
        
            $response =  $this->makeRequest($data,"");
          
            Log::info("Crea orden");

            return $response;

        }catch(\Exception $exception){
           
            Log::info("Error en createDelivery");
            $error = $this->parseError( $exception->getMessage());
          
            Log::info($error);
            $options = [
                "json" => $data,
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json',
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
            "client_id" => 'required',
            "client_secret" => 'required',
            "api_key_google" => 'required',
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


    private function generateFileZPL($archivo){

        $ZPL = new ZPL();
        $zplResult='';

        $file = base64_decode($archivo);
        
        $zplResult = $ZPL->convertirZPL($file,"pdf", $this->pedido);

        return $zplResult;

    }


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