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
use App\ClaroEnvios\ZPL\ZPL;

/**
 * Class MensajeriaTookan
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 * @author Roberto Martinez
 */
class MensajeriaTookan extends MensajeriaMaster implements MensajeriaCotizable
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
    private $equipo;
    private $pedido;
    private $tipo;

    private $keys;
    private $code_response;
    protected $id_configuracion;

    private $arrayLabelUrl = [
        'PRODUCCION'=>"https://api.tookanapp.com/v2/create_task",
        'TEST'=>"https://api.tookanapp.com/v2/create_task"
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
            $this->equipo = $mensajeriaTO->getEquipo();
            $this->pedido = $mensajeriaTO->getPedido();
            $this->tipo = $mensajeriaTO->getTipo();
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();

            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());

            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];

                if($mensajeriaTO->getNegociacionId() == 1){
                    $this->configurarAccesos($accesoComercioMensajeriaTO);
                }

                $this->keys = $this->getPoduccionKeys();
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
                $this->keys = $this->getTestKeys();
            }
        }
    }
    // Otorga una guia en PDF
    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO){
        $destino = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $origen = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $array=[];
        $linkTrack = "";

        if ($guiaMensajeriaTO->getGenerarRecoleccion()) {

            $label = $this->pickup($guiaMensajeriaTO, $destino, $origen);

            $array['recoleccion'] = (object) [
                'pick_up' => $label->data->pickup_job_id,
                'localizacion' => (env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION'),
                'response' => $this->response,
                'request' => $this->request,
            ];
            // $array['link_rastreo_entrega'] = $label->data->delivery_tracing_link;
            // $array['link_rastreo_pickup'] = $label->data->pickup_tracking_link;
            $linkTrack = $label->data->delivery_tracing_link;
            // se sustituye link de rastreo
            $array['link_rastreo_pickup'] = env('TRACKING_LINK_T1ENVIOS')."".$label->data->pickup_tracking_link;
        }
        else{

            $label = $this->delivery($guiaMensajeriaTO, $destino);
            // $array['link_rastreo_entrega'] = $label->data->tracking_link;
            $linkTrack = $label->data->tracking_link;
        }

        //Intenta crear ZPL
        $file =  $this->generateFileZPL($label->data,$guiaMensajeriaTO, $cotizacion);
        $extension = ".pdf";
        if($guiaMensajeriaTO->getTipoDocumento() == 'zpl'){
            $extension = ".zpl";
        }
        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath.('/'.$file['guia'].'_'.date('YmdHis').'.'.$this->extension_guia_impresion);
        file_put_contents($rutaArchivo, $file['data']);
        $guia = $file['guia'];
        $nombreArchivo = $guia.'_'.date('YmdHis').$extension;
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($file['data']) : base64_encode($file['data']);

        $array['guia']=$guia;
        $array['imagen']=$dataFile;
        $array['extension']="pdf";
        $array['nombreArchivo']=$nombreArchivo;
        $array['ruta']=$rutaArchivo;
        $array['location']=(env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION');
        // se sustituye link de rastreo
        $array['link_rastreo_entrega'] = env('TRACKING_LINK_T1ENVIOS')."".$guia;
        $array['infoExtra']=[
            'codigo'=>6,
            'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
            'identificadorUnico'=>'',
            // 'tracking_link' => $linkTrack
            'tracking_link' =>env('TRACKING_LINK_T1ENVIOS')."".$guia
        ];
        // $array = [
        //     'guia'=>$guia,
        //     'imagen'=>utf8_encode($file['data']),
        //     'extension'=>"pdf",
        //     'nombreArchivo' => $nombreArchivo,
        //     'ruta' => $rutaArchivo,
        //     'location' => (env('API_LOCATION') == 'test')?$this->endpointLabel:env('API_LOCATION')
        // ];

        return $array;

    }

    // consulta precios de envios
    public function rate($traerResponse = false){}

    private function getTestKeys(){

        return collect([
            "key" => '53676184f942094214192d664f507d471ae2c2fd2fda783b541d03c0',
        ]);
    }

    private function getPoduccionKeys(){

        return collect([
            "key" => '50616282f9435f19484d786b115725401ce6c4f329d47c395b1406',
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

    //rastrwr guía
    public function rastreoGuia()
    {
        $responseTrack = New ResponseTrack();
        self::setWsdl($this->endpointConsulta);
        $this->service = InstanceSoapClient::init();
        $data = [
            "api_key" => $this->keys->get('key'),
            "job_ids" => $this->guia_mensajeria->guia,
            "include_task_history" => 1

        ];

        $response = $this->service->ExecuteQuery($data);

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
        $$recoleccion = new \stdClass();
        $recoleccion->mensaje = "Servicio no disponible";
        return $recoleccion;
    }

    public function delivery(GuiaMensajeriaTO $guiaMensajeriaTO, $destino)
    {
        $date = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
        $contenido = [
            'api_key' => $this->keys->get('key'),
            'order_id' => $this->pedido,
            'job_description' => $guiaMensajeriaTO->getContenido(),
            'customer_email' => $destino->getEmail(),
            'customer_username' => "{$destino->getNombre()} {$destino->getApellidos()}",
            'customer_phone' => $destino->getTelefono(),
            'customer_address' => "{$destino->getDireccionCompuesta()}, {$destino->getColonia()}, {$this->codigo_postal_destino}, {$destino->getMunicipio()}, {$destino->getEstado()}",
            'latitude' => '',
            'longitude' => '',
            'job_delivery_datetime' => $date,
            'custom_field_template' => 'Precio',
            'meta_data' => [
                [
                    "label"=> "precio",
                    "data"=>  $this->valor_paquete
                ]
            ],
            'team_id' => $this->equipo,
            'auto_assignment' => 1,
            'has_pickup' => "0",
            'has_delivery' => "1",
            'layout_type' => "0",
            'tracking_link' => 1,
            'timezone' => 300,
            'fleet_id' => "",
            'ref_images' => [],
            'notify' => 1,
            'tags' => "",
            'geofence' => 0,
        ];

        $label = $this->generarTookan($contenido);

        if (isset($label->status)) {
            if ($label->status !== 200) {
                $codigo = $label->status;
                $mensaje = $label->message;

                throw new ValidacionException($codigo.': '.$mensaje);
            }
        }
        return $label;
    }

    public function pickup(GuiaMensajeriaTO $guiaMensajeriaTO, $destino, $origen)
    {
        $date = Carbon::now()->endOfDay()->format('Y-m-d H:i:s');
        $contenido = [
            'api_key' => $this->keys->get('key'),
            'order_id'=> $this->pedido,
            'team_id'=> $this->equipo,
            'auto_assignment'=> '1',
            'job_description'=> $guiaMensajeriaTO->getContenido(),
            'job_pickup_phone'=> $origen->getTelefono(),
            'job_pickup_name'=> "{$origen->getNombre()} {$origen->getApellidos()}",
            'job_pickup_email'=> $origen->getEmail(),
            'job_pickup_address'=> "{$origen->getDireccionCompuesta()}, {$origen->getColonia()}, {$this->codigo_postal_origen}, {$origen->getMunicipio()}, {$origen->getEstado()}",
            'job_pickup_latitude'=> '',
            'job_pickup_longitude'=> '',
            'job_pickup_datetime'=> $date,
            'customer_email'=> $destino->getEmail(),
            'customer_username'=> "{$destino->getNombre()} {$destino->getApellidos()}",
            'customer_phone'=> $destino->getTelefono(),
            'customer_address'=> "{$destino->getDireccionCompuesta()}, {$destino->getColonia()}, {$this->codigo_postal_destino}, {$destino->getMunicipio()}, {$destino->getEstado()}",
            'latitude'=> '',
            'longitude'=> '',
            'job_delivery_datetime'=> $date,
            'has_pickup'=> '1',
            'has_delivery'=> '1',
            'layout_type'=> '0',
            'tracking_link'=> 1,
            'timezone'=> '300',
            'custom_field_template'=> '',
            'meta_data'=> [
                [
                    "label"=> "precio",
                    "data"=>  $this->valor_paquete
                ]
            ],
            'pickup_custom_field_template'=> '',
            'pickup_meta_data'=> [
                [
                    "label"=> "precio",
                    "data"=>  $this->valor_paquete
                ]
            ],
            'fleet_id'=> '',
            'p_ref_images'=> [],
            'ref_images'=> [],
            'notify'=> 1,
            'tags'=> '',
            'geofence'=> 0,
            'ride_type'=> 0
        ];
        $label = $this->generarTookan($contenido);

        if (isset($label->status)) {
            if ($label->status !== 200) {
                $codigo = $label->status;
                $mensaje = $label->message;

                throw new ValidacionException($codigo.': '.$mensaje);
            }
        }
        return $label;
    }

    /**
     * @return mixed
     */
    public function getGuiaMensajeria()
    {
        return $this->guia_mensajeria;
    }

    private function generarTookan($data){
        $requestBody = json_encode($data);
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);


                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);


        curl_setopt($ch, CURLOPT_URL, $this->endpointLabel);

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "cache-control: no-cache",
            'Content-type: application/json;charset="utf-8"'
        ));
        $this->setRequest($requestBody);

        $output = curl_exec($ch);

        $this->setResponse($output);
        $info = curl_getinfo($ch);
        $this->setCodeResponse($info['http_code']);
        $response = json_decode($output);

        return $response;
    }

    private function generateFileZPL($response,$guiaMensajeriaTO, $cotizacion){

        $noGuia = (isset($response->delivery_job_id)) ? (string)$response->delivery_job_id : (string)$response->job_id;
        $details = $this->generarDetailTookan($noGuia);
        $barcode = '';
        if (isset($details->data['0'])) {
            if (isset($details->data['0']->barcode)) {
                if ($details->data['0']->barcode !== null) {
                $barcode = $details->data['0']->barcode;
                }
            }
        }
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

    private function generarDetailTookan($guia){
        $data = [
            'api_key' => $this->keys->get('key'),
            "job_ids"=> [$guia],
            "include_task_history"=> 1
        ];

        $requestBody = json_encode($data);

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_ENCODING, "");
        curl_setopt($ch, CURLOPT_MAXREDIRS, 10);
        curl_setopt($ch, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_1);


                curl_setopt($ch, CURLOPT_POST, 1);
                curl_setopt($ch, CURLOPT_POSTFIELDS, $requestBody);


        curl_setopt($ch, CURLOPT_URL, 'https://api.tookanapp.com/v2/get_job_details');

        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            "cache-control: no-cache",
            'Content-type: application/json;charset="utf-8"'
        ));
        $this->setRequest($requestBody);

        $output = curl_exec($ch);

        $this->setResponse($output);
        $response = json_decode($output);

        return $response;
    }


    public function getCodeResponse()
    {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void
    {
        $this->code_response = $codeResponse;
    }

}
