<?php
namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Mensajerias\Accesos\AccesoCampoMensajeria;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeria;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaResponse;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Sepomex\CodigoPostalZona;
use App\ClaroEnvios\TabuladoresMensajerias\Tabulador;
use App\ClaroEnvios\Uber\TiendaUber;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\ZPL\ZPL;
use GuzzleHttp\Client;
use Illuminate\Support\Carbon;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use SKAgarwal\GoogleApi\PlacesApi;
use \Illuminate\Support\Facades\Log;
use stdClass;

class MensajeriaIvoy extends MensajeriaMaster  implements MensajeriaCotizable
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
    private $endpointDelivery;
    private $endpointLogin;
    protected $porcentaje_calculado;
    protected $costo_calculado;
    protected $seguro;
    private $equipo;
    private $pedido;
    private $tipo;
    private $ID;
    private $comercioNegociacionID;
    private $custom;
    private $url_tracking;
    private $comercio_id;

    private $keys;
    private $tiendaId;
    private $code_response;
    protected $costo_seguro;
    protected $costo_zona_extendida;
    protected $numero_externo;
    protected $id_configuracion;


    private $endpointLabel = "https://api.ivoy.mx/graphql/";

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
            $this->comercio_id = $mensajeriaTO->getComercio();
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
        $delivery = $this->createDeliveryV2($destino, $guiaMensajeriaTO, $origen, $cotizacion);

        //Intenta crear ZPL
        $responseLabel = $delivery->data->createDeliveryWithLabel->label;
        if (strpos($responseLabel, ',') !== false) {
            @list($encode, $responseLabel) = explode(',', $responseLabel);
        }

        $file = base64_decode($responseLabel);
        $tmpPath = sys_get_temp_dir();
        $trackingNUmber = $delivery->data->createDeliveryWithLabel->trackingNumber;
        $rutaArchivo = $tmpPath.('/'.$trackingNUmber.'_'.date('YmdHis').'.'.$this->extension_guia_impresion);
        file_put_contents($rutaArchivo, $file);

        $nombreArchivo = $trackingNUmber.'_'.date('YmdHis').'.pdf';
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file) : base64_encode($file);

        $array['guia']=$trackingNUmber;
        $array['imagen']=$dataFile;
        $array['extension']="pdf";
        $array['nombreArchivo']=$nombreArchivo;
        $array['ruta']=$rutaArchivo;
        $array['link_rastreo_entrega'] = env('TRACKING_LINK_T1ENVIOS')."".$trackingNUmber;
        // $array['link_rastreo_entrega'] = $delivery->data->createDeliveryWithLabel->trackingUrl;
        $array['location']=(env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION');
        $array['infoExtra']=[
            'codigo'=>'C',
            'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
            'identificadorUnico'=>'',
            'tracking_link' =>env('TRACKING_LINK_T1ENVIOS')."".$trackingNUmber
            // 'tracking_link' =>$delivery->data->createDeliveryWithLabel->trackingUrl
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
                $user = $this->configuracion->get('x-ivoy-user');

                if($token) {
                    $options = [
                        "json" => $data,
                        'connect_timeout' => 90,
                        'http_errors' => true,
                        'verify' => false,
                        'headers' => [
                            'Content-Type' => 'application/json',
                            'Authorization' => 'Bearer ' . $token,
                            'x-ivoy-user' => $user
                        ]
                    ];
                    $client = new Client();
                    $response = $client->request($method, $this->endpointLabel . $type, $options);
                    $statusResponse = $response->getStatusCode();
                    Log::info($statusResponse);
                    $content = $response->getBody()->getContents();
                    //Datos necesario para guardar log
                    $this->setResponse($content);
                    $this->setCodeResponse($statusResponse);;
                    $responseLog = json_decode($content);
                    Log::info("Request done");

                    return $responseLog;


                }else{

                    throw new \Exception("Token caducado");

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
    public function createDelivery(BitacoraMensajeriaDestinoTO $destino, GuiaMensajeriaTO $guiaMensajeriaTO,$origen, $cotizacion)
    {

        Log::info("Entra en createDelivery");

        $tienda ="";
        $cordLongPickup = 0;
        $cordLatPickup = 0;
        $cordLongDropoff = 0;
        $cordLatDropoff = 0;

        if(!empty($this->custom)){

            foreach ($this->custom as $key => $value) {

                if ($value["nombre"] == "tienda") {
                    $tienda = $value["valor"];
                }
                if ($value["nombre"] == "longitud_pickup") {
                    $cordLongPickup = $value["valor"];
                }
                if ($value["nombre"] == "latitud_pickup") {
                    $cordLatPickup = $value["valor"];
                }
                if ($value["nombre"] == "longitud_dropoff") {
                    $cordLongDropoff = $value["valor"];
                }
                if ($value["nombre"] == "latitud_dropoff") {
                    $cordLatDropoff = $value["valor"];
                }

            }
        }

        $dir_origen = array(
            "contact" => array (
                "name"  => "{$origen->getNombre()} {$origen->getApellidos()}",
                "phone" => $origen->getTelefono(),
                "email" => $origen->getEmail()
            ),
            "location" => array(
                "street"         => $origen->getCalle(),
                "externalNumber" => $origen->getNumero(),
                "zipCode"        => $this->codigo_postal_origen,
                "latitude"       => (float) $cordLatPickup,
                "longitude"      => (float)$cordLongPickup,
                "instructions"   => "Recolecta en el area de recibo de la tienda Claro Shop. Pregunta por ".$origen->getNombre()." ".$origen->getApellidos()
            )
        );

        $dir_destino = array(
            "contact" => array (
                "name"  => "{$destino->getNombre()} {$destino->getApellidos()}",
                "phone" => $destino->getTelefono(),
                "email" => $destino->getEmail()
            ),
            "location" => array(
                "street"         => $destino->getCalle(),
                "externalNumber" => $destino->getNumero(),
                "neighborhood"   => $destino->getColonia(),
                "zipCode"        => $this->codigo_postal_destino,
                "latitude"       => (float) $cordLatDropoff,
                "longitude"      => (float) $cordLongDropoff,
                "instructions"   => $destino->getReferencias()
            )
        );

        $productos = [];

        $pv = ($this->largo * $this->ancho * $this->largo)/5000;
            //$size = ($pv < 7)? 'SMALL_BOX': ($pv < 15)? 'MEDIUM_BOX' : 'BIG_BOX' ;
            $size = ($pv < 7) ? 'SMALL_BOX' : (($pv < 15) ? 'MEDIUM_BOX' : 'BIG_BOX');

            if($pv > 30){
                $productos = [
                    "size" => "SOBREPESO"
                ];
            }
            else {
                $productos = [
                    [
                        "size" => $size,
                        "dimensions" => [
                            "height"  => (float)$this->alto,
                            "width"   => (float)$this->ancho,
                            "length"  => (float)$this->largo,
                            "weight"  => (float)$this->peso
                        ],
                        "items" => [
                            'description' => $guiaMensajeriaTO->getContenido(),
                            'quantity'    => 1
                        ]
                    ]
                ];
            }

        $data = array(
                "input" => array(
                    "type"        => $cotizacion->getTipoServicio(),
                    "referenceId" => $this->pedido,
                    "storeId"     => $tienda,
                    "pickup"      => $dir_origen,
                    "dropoff"     => $dir_destino,
                    "packages"    => $productos
                )
            );

        $this->setRequest(json_encode($data));
        Log::info(json_encode($data));
        $response =  $this->makeRequest($data,"create-delivery-with-label");

        Log::info("Crea orden: ".$response->trackingNumber);
        return $response;
    }

    public function createDeliveryV2(BitacoraMensajeriaDestinoTO $destino, GuiaMensajeriaTO $guiaMensajeriaTO,$origen, $cotizacion)
    {

        Log::info("Entra en createDeliveryV2");

        $tienda ="";
        $cordLongPickup = 0;
        $cordLatPickup = 0;
        $cordLongDropoff = 0;
        $cordLatDropoff = 0;

        if(!empty($this->custom)){

            foreach ($this->custom as $key => $value) {

                if ($value["nombre"] == "tienda") {
                    $tienda = $value["valor"];
                }
                if ($value["nombre"] == "longitud_pickup") {
                    $cordLongPickup = $value["valor"];
                }
                if ($value["nombre"] == "latitud_pickup") {
                    $cordLatPickup = $value["valor"];
                }
                if ($value["nombre"] == "longitud_dropoff") {
                    $cordLongDropoff = $value["valor"];
                }
                if ($value["nombre"] == "latitud_dropoff") {
                    $cordLatDropoff = $value["valor"];
                }

            }
        }

        $dir_origen = array(
            "contact" => array (
                "name"  => "{$origen->getNombre()} {$origen->getApellidos()}",
                "phone" => $origen->getTelefono(),
                "email" => $origen->getEmail()
            ),
            "location" => array(
                "street"         => $origen->getCalle(),
                "externalNumber" => $origen->getNumero(),
                "zipCode"        => $this->codigo_postal_origen,
                "latitude"       => (float) $cordLatPickup,
                "longitude"      => (float)$cordLongPickup,
                "instructions"   => "Recolecta en el area de recibo de la tienda Claro Shop. Pregunta por ".$origen->getNombre()." ".$origen->getApellidos()
            )
        );

        $pedido = $this->pedido ? "(".$this->pedido.")"." "  :'';

        $dir_destino = array(
            "contact" => array (
                "name"  => $pedido." "."{$destino->getNombre()} {$destino->getApellidos()}",
                "phone" => $destino->getTelefono(),
                "email" => $destino->getEmail()
            ),
            "location" => array(
                "street"         => $destino->getCalle(),
                "externalNumber" => $destino->getNumero(),
                "neighborhood"   => $destino->getColonia(),
                "zipCode"        => $this->codigo_postal_destino,
                "latitude"       => (float) $cordLatDropoff,
                "longitude"      => (float) $cordLongDropoff,
                "instructions"   => $destino->getReferencias()
            )
        );

        $productos = [];

        $pv = round(($this->largo * $this->ancho * $this->largo)/5000,2);
        if($pv == 0)  $pv = 0.01;

        $size = 'BIG_BOX' ;

        if($pv <= 7){
            $size = "SMALL_BOX";
        }
        if($pv>=8 && $pv <= 15){
            $size = "MEDIUM_BOX";
        }
        error_log("SIZE FINAL: ".$size);


        $productos = array(
                "cartaPorte" => array(
                    "propertyCarried" => "01010101",
                    "keyUnitWeight"   => "Tu"
                ),
                "declaredValue" => $this->valor_paquete,
                "weight" => array(
                    "gross" => (float)number_format($pv,2,'.',''),
                    "net"   => (float)number_format($pv,2,'.',''),
                    "tare"  => (float)number_format($pv,2,'.','')
                ),

                "size" => $size,
                "dimensions" => [
                    "height"  => (float)$this->alto,
                    "width"   => (float)$this->ancho,
                    "length"  => (float)$this->largo,
                    "weight"  => (float)$this->peso
                ],
                "items" => [
                    'description' => $guiaMensajeriaTO->getContenido(),
                    'quantity'    => 1
                ]
        );

        $query_data = 'mutation createDeliveryWithLabel($inputDeliveryWithLabel: CreateDeliveryInput!){createDeliveryWithLabel(input: $inputDeliveryWithLabel){id trackingNumber trackingUrl referenceId label(type: PDF)}}';

        $data = array(
            "query" => $query_data,
            "variables" => array(
                "inputDeliveryWithLabel" => array(
                    "type"        => $cotizacion->getTipoServicio(),
                    "referenceId" => $this->pedido,
                    "storeId"     => $tienda,
                   // "pickup"      => $dir_origen,
                    "dropoff"     => $dir_destino,
                    "packages"    => $productos
    )
            )
        );

        $this->setRequest(json_encode($data));
        Log::info(json_encode($data));
        $response =  $this->makeRequest($data,'');
//        Log::info("Response Ivoy: ");
//        Log::info(json_encode($response));
        Log::info("Crea orden: ".$response->data->createDeliveryWithLabel->trackingNumber);
        return $response;
    }

/*
    private function generateFileZPL($noGuia){

        $ZPL = new ZPL();
        $zplResult='';
        $cadena='';

        $token = $this->configuracion->get('token');
        try{
            $options=[
                "json"=>"",
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

            Log::info("RESPONSE ZPL:"); Log::info($response);

            }catch (\Exception $exception){
                Log::info("ERROR peticion ZPL:");
                Log::info($exception->getMessage());

                throw new \Exception($exception->getMessage(),$exception->getCode());
            }

        if($response){
            $ruta ="";
            foreach ($this->custom as $key => $value) {

                if ($value["nombre"] == "ruta") {
                    $ruta = $value["valor"];
                }

            }


            $cadena = "^CF0,70
            ^FO400,1402^FD".$ruta."^FS";
            $file = substr_replace($response, $cadena,-75, -74);
            $zplResult = $ZPL->convertirZPL($file,"pdf", $noGuia);

        }

        return $zplResult;

    }
*/

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

    public function getCodeResponse()
    {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void
    {
        $this->code_response = $codeResponse;
    }

    // consulta precios de envios
    public function rate($traerResponse = false)
    {
//        try{
////
//            $user = Auth::user();
////            die(print_r($this));
//            $comercioIdTabulador = $this->negociacion_id == 1 ? 6 : $this->comercio_id;
//            $tarificador = new stdClass();
//            $tarificador->success = true;
//            $tarificador->message = Response::$messages['successfulSearch'];
//            $tarificador->code_response = 200;
//            $tarificador->servicios = new stdClass();
//
//            if ($this->negociacion_id == 1) {
//
//                Log::info("Comercio: " . $this->comercio_id);
//                Log::info("Comercio Negociacion: " . $comercioIdTabulador .", si es 6 es claroshop");
//                $pesoVolumetrico = number_format(($this->largo * $this->ancho * $this->alto) / 5000, 1, '.', '');
//
////                $decimals = explode('.', $pesoVolumetrico);
//                $pesoVolumetrico = ceil($pesoVolumetrico);
//                Log::info("peso volumetrico: ".$pesoVolumetrico);
//
//                if ($pesoVolumetrico == 0) $pesoVolumetrico = 1;
//
//                $zonaEnvio = CodigoPostalZona::where('codigo_postal', $this->codigo_postal_origen)->where('mensajeria_id', $this->ID)
//                    ->where('comercio_id', $comercioIdTabulador)
//                    ->first();
//
//                $zonaRecepcion = CodigoPostalZona::where('codigo_postal', $this->codigo_postal_destino)->where('mensajeria_id', $this->ID)
//                    ->where('comercio_id', $comercioIdTabulador)
//                    ->first();
//
//                $tabuladores = collect();
//                if ($zonaRecepcion && $zonaEnvio) {
//
//                    $tabuladores = Tabulador::with('servicio:id,descripcion,nombre')
//                        ->select('tabuladores.*', DB::raw('mensajerias.descripcion as mensajeria'))
//                        ->join('mensajerias', 'tabuladores.mensajeria_id', '=', 'mensajerias.id')
//                        ->where('zona_envio', $zonaEnvio->zona)
//                        ->where('zona_recepcion', $zonaRecepcion->zona)
//                        ->where('mensajeria_id', $this->ID)
//                        ->where('peso', $pesoVolumetrico)
//                        ->get();
//
//                }
//
//
//                if ($tabuladores->count() > 0) {
//
//                    foreach ($tabuladores as $tabulador) {
//
//                        Log::info("Servicio " . $tabulador->servicio->nombre);
//                        $costo = $tabulador->precio;
//                        $costoAdicional = 0;
//                        if ($this->costo != 0) {
//                            $costoAdicional = $this->costo;
//                        } elseif ($this->porcentaje != 0) {
//                            $costoAdicional = round($costo * ($this->porcentaje / 100), 4);
//                        }
//                        $costoSeguro = $this->seguro ? round($this->valor_paquete * ($this->porcentaje_seguro / 100), 4) : 0;
//                        Log::info(' Costo Serguro ' . $costoSeguro);
//                        Log::info(' Costo Adicional ' . $costoAdicional);
//
//                        $costoTotalCalaculado = round($costo + $costoAdicional + $costoSeguro, 4);
//                        $servicioMensajeria = $this->obtenerServicioMensajeria('IVOY', $tabulador->servicio->nombre);
//                        $servicio = $this->responseService($costo, $costo, $costoTotalCalaculado, $servicioMensajeria);
//
//                        if ($servicio) {
//                            $tarificador->servicios->{$tabulador->servicio->nombre} = $servicio;
//                        }
////                        die(print_r($tabulador->servicio->nombre));
//                        $tarificador->location = $this->location;
//                        $tarificador->request = json_encode(["comercio"=> $this->comercio_id,"codigo_postal_origen"=>$this->codigo_postal_origen,"codigo_postal_destino"=>$this->codigo_postal_destino]);
//                        $tarificador->response = json_encode(["success"=>"ok","message"=>"Consulta tarificador tabulador correctamente"]);
//
//                    }
//                }else{
//                    $tarificador->success = false;
//                    $tarificador->message = Response::$messages['noCoverage'];
//                    $tarificador->code_response = 400;
//                    $tarificador->request = json_encode(["comercio"=>$this->comercio_id,"codigo_postal_origen"=>$this->codigo_postal_origen,"codigo_postal_destino"=>$this->codigo_postal_destino]);
//                    $tarificador->response = json_encode(["success"=>"error","message"=>$tarificador->message ]);
//
//                }
//
//
//            }
//
//            return $tarificador;
//        }catch (\Exception $exception){
//            Log::error($exception->getFile().': '.$exception->getLine().' '.$exception->getMessage());
//            throw new \Exception($exception->getMessage());
//        }
    }

    public function validarCampos(){
        $rules = [
            "type" => 'required'
        ];

        return $rules;
    }
}
