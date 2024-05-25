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
 * Class MensajeriaCaminamos
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 * @author Roberto Martinez
 */
class MensajeriaCaminamos extends MensajeriaMaster implements MensajeriaCotizable
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

            $this->configurarAccesos($accesoComercioMensajeriaTO);

            if(!$this->configuracion){
                $this->configuracion = collect();
                Log::info('apikey:'.$this->configuracion->get('apikey'));
            }
            Log::info('apikey:'.$this->configuracion->get('apikey'));
            $this->keys = collect(["key" => $this->configuracion->get('apikey')]);

            if ($this->location === 'produccion' || $this->location === 'release') {
                $this->endpointLabel = $this->arrayLabelUrl['PRODUCCION'];

//                if($mensajeriaTO->getNegociacionId() == 1){
//                    $this->configurarAccesos($accesoComercioMensajeriaTO);
//                }

//                $this->keys = $this->getPoduccionKeys();
            }
            else{
                $this->endpointLabel = $this->arrayLabelUrl['TEST'];
//                $this->keys = $this->getTestKeys();
            }
        }
    }

    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO){

    }

    // consulta precios de envios
    public function rate($traerResponse = false){}

    private function getTestKeys(){

        return collect([
            "key" => '666',
        ]);
    }

    private function getPoduccionKeys(){

        return collect([
            "key" => '999',
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

        if($zpl['success'] == true){
            $zplResult = $ZPL->convertirZPL($zpl["zpl"],"pdf", $noGuia);

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
