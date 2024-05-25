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
use GuzzleHttp\TransferStats;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use SKAgarwal\GoogleApi\PlacesApi;
use \Illuminate\Support\Facades\Log;

/**
 * Class MensajeriaUber
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 */
class MensajeriaUber extends MensajeriaMaster implements MensajeriaCotizable
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

    private $keys;
    private $tiendaId;
    private $code_response;

    private $comercioNegociacionID;
    protected $id_configuracion;

    private $arrayLabelUrl = [
        'PRODUCCION'=>"https://api.uber.com/v1/eats/deliveries/",
        'TEST'=>"https://api.uber.com/v1/eats/deliveries/"

    ];

    private $arrayLoginUrl = [
        'PRODUCCION'=>"https://login.uber.com/oauth/v2/token",
        'TEST'=>"https://login.uber.com/oauth/v2/token"

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
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();

            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());

            if($mensajeriaTO->getNegociacionId() == 1){
                $accesoComercioMensajeriaTO->setComercioId(1);
                $this->comercioNegociacionID = 1;
            }

            Log::info('Comercio: '.$mensajeriaTO->getComercio().', '.'Negociacion: '.$mensajeriaTO->getNegociacionId());
            Log::info('Llaves comercio: '.$this->comercioNegociacionID);

            $this->configurarAccesos($accesoComercioMensajeriaTO);

            if(!$this->configuracion){
                $this->configuracion = collect();
            }
//            $this->configuracion->put('API_KEY_GOOGLE',"AIzaSyDzyX0ZVSyqb9TxYQW9JxVW6Vb3Oc2w47Q");

            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];
                $this->endpointLogin = $this->arrayLoginUrl['PRODUCCION'];
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->endpointLogin = $this->arrayLoginUrl['TEST'];
            }

//            die(print_r($this->configuracion));
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

            $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
            $tiendaOrigen='';
            if ($this->comercioNegociacionID == 6 ) {
                $tiendaOrigen = TiendaUber::where('id_tienda', $this->tiendaId)->where('comercio_id', $this->comercioNegociacionID)->first();
            }else{
                $tiendaOrigen = TiendaUber::where('id_tienda', $this->tiendaId)->first();
            }

            if($this->configuracion->count() < 3){
                throw new \Exception("No cuenta con credenciales de mensajeria");
            }

            if(!$tiendaOrigen){
                throw new \Exception("La tienda ".$this->tiendaId." no esta dada de alta en uber" );
            }

            $array = [];

            $placeApiDestino = $this->searchPlaceID($destino);
            //Busca si hay tiendas disponibles
            $this->findStore($placeApiDestino);
            $estimate = $this->createEstimate($tiendaOrigen, $placeApiDestino, $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaId(),$this->pedido);
            $delivery = $this->createDelivery($estimate, $tiendaOrigen, $placeApiDestino, $destino, $guiaMensajeriaTO);


            //Intenta crear ZPL
            $file = $this->generateFileZPL($delivery->order_id, $guiaMensajeriaTO, $cotizacion);

            $extension = ".pdf";
            if($guiaMensajeriaTO->getTipoDocumento() == 'zpl'){
                $extension = ".zpl";
            }

            $tmpPath = sys_get_temp_dir();
            $rutaArchivo = $tmpPath . ('/' . $file['guia'] . '_' . date('YmdHis') . '.' . $this->extension_guia_impresion);
            file_put_contents($rutaArchivo, $file['data']);
            $guia = $file['guia'];
            $nombreArchivo = $guia . '_' . date('YmdHis') . $extension;
            $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file['data']) : base64_encode($file['data']);

            $array['guia'] = $guia;
            $array['imagen'] =$dataFile;
            $array['extension'] = "pdf";
            $array['nombreArchivo'] = $nombreArchivo;
            $array['ruta'] = $rutaArchivo;
            $array['link_rastreo_entrega'] = $delivery->order_tracking_url;
            $array['location'] = (env('API_LOCATION') == 'test') ? $this->endpointLabel : env('API_LOCATION');

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

            $limiteCosto = CampoLimiteMensajeria::leftjoin('configuracion_limites_mensajerias', 'campos_limites_mensajerias.id_limite_mensajeria', '=', 'configuracion_limites_mensajerias.id')
                ->where('configuracion_limites_mensajerias.id',4)
                ->where('campos_limites_mensajerias.id_comercio',auth()->user()->comercio_id)
                ->where('campos_limites_mensajerias.id_mensajeria',$this->ID)
                ->first();

//            die(print_r($costoGuia));
//            $limiteCosto = ($limitePrecio->limite_costo_envio > 0)?$costoMensajeria->limite_costo_envio:null;
            if(($limiteCosto) && ($costoGuia < $limiteCosto->min || $costoGuia > $limiteCosto->max)){
                $mensaje = "El costo de la guía $costoGuia sobrepasa el limite configurado: min:".$limiteCosto->min ." max:".$limiteCosto->max;
                throw new \Exception($mensaje);
            }

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
     * @param BitacoraMensajeriaDestinoTO $destino
     * @return Collection
     * @throws \Exception
     */
    private function searchPlaceID(BitacoraMensajeriaDestinoTO $destino){
        $apiKey = $this->configuracion->get('api_key_google');
        $place = collect();

        try{
            $direccionOk = $this->cleanAddress($destino);
            $input = urlencode($direccionOk);
            Log::info('Direccion buscada: '.$direccionOk);

            $googlePlaces = new PlacesApi($apiKey);
            $response = $googlePlaces->findPlace($input,'textquery',['fields'=>'formatted_address,place_id,types','radius'=>'150']);
//            $response = $googlePlaces->placeAutocomplete($input,['radius'=>'5000']);
            Log::info($response);

            if($response['status'] == 'OK'){
//                $predictions = $response['predictions'];
//
//                foreach ($predictions as $item){
//                    if(count($item['matched_substrings']) > 0)
//                    die(print_r($item));
//                }
                if(count($response['candidates']) > 0){
                    $placeId = $response['candidates'][0]['place_id'];
                    $types = $response['candidates'][0]['types'];
                    if(!in_array('plus_code',$types)){
                        Log::info('Places ID: '.$placeId);
                        $place->put('place_id',$placeId);

                        if(array_key_exists('formatted_address',$response['candidates'][0]))
                            $place->put('formatted_address',$response['candidates'][0]['formatted_address']);
                            $place->put('formatted_address_searched',$direccionOk);
                    }else{
                        Log::info('Types no es direccion exacta');
                        throw new \Exception("Google Places no encontró una ubicación con esta dirección");
                    }

                }


            }elseif($response['status'] == 'ZERO_RESULTS'){
                throw new \Exception("Google Places no encontró una ubicación con esta dirección");
            }else{
                throw new \Exception($response['status']);
            }
        }catch (\Exception $exception){
            throw new \Exception($exception->getMessage());
        }
//                    die(print_r($place));
        return $place;
    }

    /**
     * Limpia direccion
     * @param $address
     * @return string|string[]|null
     */
    private function cleanAddress(BitacoraMensajeriaDestinoTO $destino){

        $repetido = false;
        $numero = (int) filter_var($destino->getNumero(), FILTER_SANITIZE_NUMBER_INT);

        if($numero != 0){
            $calle = collect(explode(' ',$destino->getCalle()));
            $numerosCalle = $calle->map(function ($value) use ($numero){
                $value = (int) filter_var($value, FILTER_SANITIZE_NUMBER_INT);
                return $value;
            });
            $repetido = $numerosCalle->search($numero, $strict = true);
        }

//                die(var_dump($repetido));
        if($repetido >= 0 && $repetido !== false){
            $direccion = $destino->getCalle()." ".$destino->getColonia()." ".$this->codigo_postal_destino." ".
                $destino->getMunicipio()." ".$destino->getEstado();
            Log::info('Direccion se omite numero: '.$direccion);
        }else{
            $direccion = $destino->getCalle()." ".$destino->getNumero()." ".$destino->getColonia()." ".$this->codigo_postal_destino." ".
                $destino->getMunicipio()." ".$destino->getEstado();
            Log::info('Direccion con numero: '.$direccion);

        }

        $nuevaDireccion = $direccion;

        $parentesis1= strpos($nuevaDireccion,"(");
        $parentesis2= strpos($nuevaDireccion,")");

        //revisa si hay parentesis y elimina el texto dentro de el
        if($parentesis1 && $parentesis2){
            $textoParentesis = substr($nuevaDireccion,$parentesis1,$parentesis2-$parentesis1+1);
            $nuevaDireccion = str_replace($textoParentesis,'',$nuevaDireccion);
        }

        //solo texto y numeros
        $direccionSola = preg_replace('([^A-Za-z0-9 #.,áéíóúÁÉÍÓÚñÑ])', '', $nuevaDireccion);

        $direccionSola = str_replace("Int",'',$direccionSola);

        $direccionSola = str_replace("Sin datos",'',$direccionSola);

        //quita espacios
        $direccion = preg_replace(array('/\s{2,}/', '/[\t\n]/'), ' ', $direccionSola);

        return $direccion;

    }

    /**
     * @param $data
     * @param string $type estimate|orders
     * @param string $method
     * @return mixed
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    private function makeRequest($data,$type, $method = 'POST',$bitacoraCotizacionId = null){

        $token = $this->configuracion->get('token');
//        if(strstr($type, 'store')){
//            $token = '123';
//        }
//        if(($type == 'estimates')){
//            $token = '123';
//        }
//                if(($type == 'orders')){
//            $token = '123';
//        }
        Log::info('TOKEN makeRequest: '.$token);

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

        try{

            if ($token) {
                $client = new Client();
                $response = $client->request($method, $this->endpointLabel . $type, [
                    "json" => $data,
                    'connect_timeout' => 90,
                    'http_errors' => true,
                    'verify' => false,
                    'headers' => [
                        'Content-Type' => 'application/json',
                        'Authorization' => 'Bearer ' . $token
                    ],
                    'on_stats' => function (TransferStats $stats) use($method, $type) { //se obtiene tiempo de ejecución de petición
                        Log::info("TIEMPO ".$method. " - ".$this->endpointLabel ."".$type." : ". $stats->getTransferTime());
                    }
                ]);
                $statusResponse = $response->getStatusCode();
                $content = $response->getBody()->getContents();

                $this->setResponse($content);
                $this->setRequest(json_encode($options));
                $this->setCodeResponse($statusResponse);
//                die(print_r(json_decode($content)));
//            $timestamp = $response->estimates[0]->pickup_at;
//            $date = date("d M Y H:i", $timestamp);
                return json_decode($content);

            } else {

                Log::info("Primer Token :");
                $accesoCampo = AccesoCampoMensajeria::where('mensajeria_id', $this->ID)
                    ->where('clave', 'token')
                    ->first();

                $accesoComercio = AccesoComercioMensajeria::where('mensajeria_id',$this->ID)
                    ->where('comercio_id',$this->comercioNegociacionID)
                    ->where('acceso_campo_mensajeria_id',$accesoCampo->id)
                    ->first();

                $token = $this->getToken('client_credentials');

                if($accesoComercio){
                    $this->actualizaToken($token);
                }else{
                    $this->guardaToken($token, $accesoCampo);
                }

                $this->configuracion->put('token', $token);
                $response = $this->requestUber($data, $type, $method,$bitacoraCotizacionId);

                return $response;

            }

        }catch (\Exception $exception){
            Log::error("Error makeRequest: ".$exception->getMessage().': '.$exception->getFile().' '.$exception->getLine());
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

                    $tokenUber = $this->getToken('client_credentials');
                    $this->actualizaToken($tokenUber);

                    $response= $this->requestUber($data,$type,$method,$bitacoraCotizacionId);

                }
                else{
                    Log::info("Nuevo Token:");
                    $token = $this->getToken('client_credentials');
                    $this->guardaToken($token,$accesoCampo);

                    $this->configuracion->put('token',$token);
                    $response= $this->requestUber($data,$type,$method,$bitacoraCotizacionId);
                }

                return $response;

            }else{

                throw new \Exception($exception->getMessage(), $exception->getCode());
            }
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

    private function getToken($grantType,$token = null){
        try{
            Log::info("client_id: ".$this->configuracion->get('client_id'));
            $options=[
                "form_params"=> [
                    "client_id" => $this->configuracion->get('client_id'),
                    "client_secret" =>$this->configuracion->get('client_secret'),
                    "grant_type" => $grantType,
                    "scope" => "eats.deliveries"
                ],
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers'  => [
                    'Content-Type'=>'application/x-www-form-urlencoded'
                ]
            ];

//            if($token)
//                $options["form_params"]["refresh_token"] = $token;
            Log::info("Genera nuevo token");
            $client = new Client();
            $response = $client->request("POST", $this->endpointLogin,$options)->getBody()->getContents();
            $result = json_decode($response);
            Log::info("RESPONSE TOKEN:");
            Log::info($response);

            return $result->access_token;

        }catch (\Exception $exception){
            Log::error("ERROR getToken:");
            Log::error($exception->getMessage());
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
    private function requestUber($data,$type,$method, $bitacoraCotizacionId = null){
        $token = $this->configuracion->get('token');
        Log::info("Peticion con nuevo token " . $this->endpointLabel . $type . ": " . $this->configuracion->get('token'));
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
//            die(print_r($response));
            return $responseLog;

        } catch (\Exception $exception) {
            $responseError = $exception->getMessage();
            Log::info("ERROR nueva peticion: " . $responseError);
            throw new \Exception($responseError, $exception->getCode());
        }
    }

    private function parseError($responseError){
       $json = $responseError;
        $responseUber = strpos($responseError,'response');

        if($responseUber){
            $json = substr($responseError,$responseUber +10);
            $messageObject= json_decode($json);
            if(property_exists($messageObject,'message')){
                $responseError = $messageObject->message;
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
    public function createDelivery($estimate,TiendaUber $tiendaOrigen,Collection $placeApiDestino,BitacoraMensajeriaDestinoTO $destino, GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        try{

            Log::info("Entra en createDelivery");
            Log::info("Client_id: ".$this->configuracion->get('client_id'));

            $valorPaque = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO()->getValorPaquete();
            $estimateId = $estimate->estimate_id;
            $user = auth()->user();
            $pickupAt = $estimate->estimates[0]->pickup_at;
            $nombreProducto = $guiaMensajeriaTO->getContenido();

            if(in_array(auth()->user()->comercio_id,[5,6,7] )){
                $nombreProducto = "Paquete";
            }

            $data = [
                "courier_tip"=> 0,
                "currency_code"=> "MXN",
                "dropoff" => [
                    "address"=> [
                        "apt_floor_suite"=> null,
                        "place"=> [
                            "id"=> $placeApiDestino->get('place_id'),
                            "provider"=> "google_places"
                        ]
                    ],
                    "contact"=> [
                        "email"=> $destino->getEmail(),
                        "first_name"=> $destino->getNombre(),
                        "last_name"=> $destino->getApellidos(),
                        "phone"=> "+52".$destino->getTelefono(),//e164 format
                    ],
                    "instructions"=> $placeApiDestino->get('formatted_address_searched').' '.$destino->getReferencias(),
                    "type"=> "DOOR"
                ],
                "dropoff_verification"=> [
                    "signature"=> true,
                    "picture"=> true
                ],
                "estimate_id"=> $estimateId,
                "external_order_id"=> $this->pedido,
                "external_user_id"=> "$user->id",
                "order_items"=> [
                    [
                        "currency_code"=> "MXN",
                        "description"=> $nombreProducto,
//                    "external_id"=> "",
                        "name"=> $nombreProducto,
                        "price"=> floatval($valorPaque),
                        "quantity"=> 1
                    ]],
                "order_value"=> floatval($valorPaque),
                "pickup"=> [
                    "external_store_id"=>strval($tiendaOrigen->id_tienda),
                    "instructions"=> null,
                    "store_id"=> "$tiendaOrigen->uuid_uber"
                ],
                "pickup_at"=> $pickupAt,
                "return_trips_enabled" => true
            ];

            $response =  $this->makeRequest($data,"orders");
            Log::info("JSON UBER DELIVERY: ");
            Log::info($data);
            Log::info("Crea orden: ".$response->order_id);

            return $response;

        }catch(\Exception $exception){
            Log::info("Error en createDelivery");
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


    private function generateFileZPL($noGuia,$guiaMensajeriaTO, $cotizacion){
        $barcode = '';

        $ZPL = new ZPL();
        $zpl = $ZPL->documentoTookan(
            $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO(),
            $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO(),
            $noGuia,
            $this->codigo_postal_destino,
            $this->codigo_postal_origen,
            $this->equipo,
            $this->pedido,
            $this->tipo,
            $cotizacion,
            $barcode
        );
        $zplResult = $zpl;

        if($zpl['success'] == true && $guiaMensajeriaTO->getTipoDocumento() != 'zpl'){
            $zplResult = $ZPL->convertirZPL($zpl["data"],"pdf", $noGuia);
        }

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


}
