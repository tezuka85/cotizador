<?php

namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraCartaPorteResponse;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigen;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\GuiaExcedente\GuiaExcedente;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponse;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Sepomex\Sepomex;
use App\ClaroEnvios\Xml\Dhl;
use App\ClaroEnvios\ZPL\ZPL;
use App\Exceptions\ValidacionException;
use Carbon\Carbon;
use FedEx\RateService\ComplexType;
use FedEx\RateService\SimpleType;
use FedEx\RateService\Request as RequestFedex;
use FedEx\TrackService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
use FedEx\ShipService;
use FedEx\ShipService\ComplexType\Money as ComplexTypeShipMoney;
/**
 * Class MensajeriaFedex
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 */
class MensajeriaFedexSoap extends MensajeriaMaster implements MensajeriaCotizable
{
    public static $arrayServicioDescripcion = [
        'FEDEX_EXPRESS_SAVER'=>'Economico',
        'STANDARD_OVERNIGHT'=>'Dia Siguiente'
    ];

    public static $arrayServiciosPermitidos = [
        'FEDEX_EXPRESS_SAVER',
        'STANDARD_OVERNIGHT'
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
    protected $formato_guia_impresion;
    protected $extension_guia_impresion;
    protected $fecha_liberacion;
    protected $request;
    protected $response;
    protected $location;
    protected $negociacion_id;
    protected $porcentaje_calculado;
    protected $costo_calculado;
    protected $costo_adicional;
    protected $id;
    protected $seguro;
    protected $code_response;
    protected $costo_seguro;
    protected $costo_zona_extendida;
    protected $numero_externo;
    protected $codigo_estado;
    protected $pais_destino;
    protected $pais_fabricacion;
    protected $categoria;
    protected $moneda;
    protected $peso_calculado;
    protected $negociacion;
    protected $comercio_id;
    protected $url_carta_porte;

    use AccesoConfiguracionMensajeria;

    const PRODUCTION_URL = 'https://ws.fedex.com:443/web-services/';
    const TESTING_URL = 'https://wsbeta.fedex.com:443/web-services/';
//    const TESTING_URL = 'https://ws.fedex.com:443/web-services/';

    const PRODUCTION_URL_CARTA_PORTE ="https://ws.fedex.com/LAC/ServicesAPI/mx/cartaporte/customers";

    const TESTING_URL_CARTA_PORTE ="https://wsbeta.fedex.com/LAC/ServicesAPI/mx/cartaporte/customers";

    //const TESTING_URL_CARTA_PORTE ="https://ws.fedex.com/LAC/ServicesAPI/mx/cartaporte/customers";


    /**
     * MensajeriaFedex constructor.
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
            $this->codigo_estado = $mensajeriaTO->getCodigoEstado();
            $this->pais_destino = $mensajeriaTO->getPaisDestino();
            $this->pais_fabricacion = $mensajeriaTO->getPaisFabricacion();
            $this->moneda = $mensajeriaTO->getMoneda();
            $this->peso_calculado = $mensajeriaTO->getPesoCalculado();
            $this->negociacion = $mensajeriaTO->getNegociacion();
            $this->comercio_id = $mensajeriaTO->getComercio();
//            $this->url_carta_porte = env('FEDEX_URL_CARTA_PORTE');
//            die(print_r($mensajeriaTO));
            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());

            //SI es negiciación 1-t1envios|4-t1envios credito se toman las llaves de mensajeria de t1envios
            if($mensajeriaTO->getNegociacionId() == 1 || $mensajeriaTO->getNegociacionId() == 4 || $mensajeriaTO->getNegociacion() == 'COM_006'){
                $accesoComercioMensajeriaTO->setComercioId(1);
            }

            $this->configurarAccesos($accesoComercioMensajeriaTO);
            if(isset($this->configuracion)) {
                if($location == 'produccion' || $location == 'release'){
                    $this->configuracion->put('location', self::PRODUCTION_URL);
                    $this->location = env('API_LOCATION');
                    $this->url_carta_porte = self::PRODUCTION_URL_CARTA_PORTE;
                }else{
                    $this->configuracion->put('location', self::TESTING_URL);
                    $this->location = env('API_LOCATION');
                    $this->url_carta_porte = self::TESTING_URL_CARTA_PORTE;
                }

            }else{
                throw new \Exception("No cuenta con llaves de accceso a la mensajeria Fedex");
            }
            Log::info('Comercio: '.$mensajeriaTO->getComercio().', '.'Negociacion: '.$mensajeriaTO->getNegociacionId());
            Log::info('Llaves comercio: '.$accesoComercioMensajeriaTO->getComercioId());

            if(!$mensajeriaTO->getTabulador()){
                Log::info( $this->configuracion);
            }
//            die(print_r($this->configuracion));
        }
    }

    public function rate($traerResponse = false,$peso=null)
    {
        $arrayServiciosPermitidos = [
            'FEDEX_EXPRESS_SAVER',
            'STANDARD_OVERNIGHT'
        ];
        $response = $this->ratePeticion();

        $rateReply = $response['rateReply'];
        $tarificador = new stdClass();
        $tarificador->success = true;
        $tarificador->message = Response::$messages['successfulSearch'];
        if ($traerResponse) {
            $tarificador->request = $response['request'];
            $tarificador->response = $response['response'];
        }
        Log::info("RESPONSE :".$rateReply->Notifications[0]->Message);
        Log::info('Estatus: '.$rateReply->HighestSeverity);
        $this->code_response = $rateReply->HighestSeverity == 'SUCCESS'?200:400;

        Log::info('RateReplyDetails');
        Log::info($rateReply->RateReplyDetails);
//        die(print_r( $response['response']));
        if (($rateReply->HighestSeverity == 'SUCCESS' || $rateReply->HighestSeverity == 'WARNING' )) {
//            $serviciosMensajerias = ServicioMensajeria::all();
            Log::info('Entra en success o warning');
            if ($rateReply->RateReplyDetails) {
                foreach ($rateReply->RateReplyDetails as $data) {
                    Log::info('Entra en RateReplyDetails');

                    if (in_array($data->ServiceType, $arrayServiciosPermitidos)) {
                        Log::info('Entra en servicios permitidos');
                        if (!empty($data->RatedShipmentDetails)) {
                            foreach ($data->RatedShipmentDetails as $ratedShipmentDetail) {
                                Log::info('Entra en servicios RateType');
                                $tipo_costo = $ratedShipmentDetail->ShipmentRateDetail->RateType;
                                Log::info($tipo_costo);
                                switch ($tipo_costo) {
                                    case 'PAYOR_LIST_SHIPMENT':
                                        $costo = $ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount;
                                        //19.92
                                        break;
                                    case 'PAYOR_ACCOUNT_SHIPMENT':
                                        $costo_claro = $ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount;
                                        //19.43
                                        break;
                                    //PREFERRED_ACCOUNT_SHIPMENT: 369.62
                                    //PREFERRED_LIST_SHIPMENT:378.94
                                }
                            }
                        }
                        if (!property_exists($tarificador, 'servicios')) {
                            Log::info('No existe servicios');
                            $tarificador->servicios = new stdClass();
                        }
                        $costoAdicional = 0;
                        $costoSeguro = 0;

                        if ($this->costo != 0) {
                            $costoAdicional = $this->costo;
                            Log::info(' Costo margen: '.$this->costo);
                        } elseif ($this->porcentaje != 0) {
                            $costoAdicional = round($costo_claro * ($this->porcentaje / 100), 2);
                            Log::info(' Porcentaje margen: '.$this->porcentaje);
                        }

                        Log::info(' Costo guia mensajeria FEDEX: '.$costo_claro);
                        if ($this->negociacion == 'COM_006') {
                            $costoGuia = $costo_claro;
                            $costoTotalCalculado = round(($costoGuia /(1-($this->porcentaje/100))) , 2);
                            Log::info('Costo Total zonas: ' . $costoTotalCalculado);

                        }else {
                            Log::info(' Calculo default: ');
                            $costoSeguro = $this->seguro ? round($this->valor_paquete * ($this->porcentaje_seguro / 100), 2) : 0;
                            Log::info(' Costo adicional calculado: '.$costoAdicional);
                            Log::info(' Costo Seguro ' . $costoSeguro);
                            $costoTotalCalculado = round($costo_claro + $costoAdicional + $costoSeguro, 2);
                            Log::info(' Costo Total: ' . $costoTotalCalculado);
                        }

                        $servicioMensajeria = $this->obtenerServicioMensajeria('Fedex', $data->ServiceType);
                        $servicio = $this->responseService($costo_claro, $costo_claro, $costoTotalCalculado, $servicioMensajeria, null, $costoSeguro);
                        $tarificador->servicios->{$data->ServiceType} = $servicio;
                        $tarificador->location = $this->location;
                    }
                }
            }
            $tarificador->code_response = $this->code_response;

            if (property_exists($tarificador, 'servicios')) {
                return $tarificador;
            }
            else{
                Log::info('Al terminar NO existe tarificador Servicios');
                $tarificador->success = false;
                $tarificador->servicios = new stdClass();
                $tarificador->codigo = $rateReply->Notifications[0]->Code;
                $tarificador->message = "No hay servicios disponibles para este requerimiento";
            }
        }else{
            $tarificador->success = false;
            $tarificador->codigo = $rateReply->Notifications[0]->Code;
            $tarificador->message = $rateReply->Notifications[0]->Message;
            $tarificador->code_response = $this->code_response;
            $tarificador->servicios = new stdClass();
        }

        return $tarificador;
    }

    public function rateInternational($traerResponse = false,$seguro = false)
    {
        $response = $this->ratePeticionInternacional($seguro);


        $rateReply = $response['rateReply'];
//        die(print_r($response));
        $tarificador = new stdClass();
        $tarificador->success = true;
        $tarificador->message = Response::$messages['successfulSearch'];
        if ($traerResponse) {
            $tarificador->request = $response['request'];
            $tarificador->response = $response['response'];
        }

        Log::info("RESPONSE :".$rateReply->Notifications[0]->Message);
        Log::info('Estatus: '.$rateReply->HighestSeverity);
        $this->code_response = $rateReply->HighestSeverity == 'SUCCESS' || $rateReply->HighestSeverity == 'NOTE' ?200:400;
        if (($rateReply->HighestSeverity == 'SUCCESS' || $rateReply->HighestSeverity == 'NOTE') && !empty($rateReply->RateReplyDetails)) {
            $serviciosMensajerias = ServicioMensajeria::all();

            foreach ($rateReply->RateReplyDetails as $data) {

//                if (in_array($data->ServiceType, $arrayServiciosPermitidos)) {

                if (!empty($data->RatedShipmentDetails)) {
                    foreach ($data->RatedShipmentDetails as $ratedShipmentDetail) {
//                        die(print_r($ratedShipmentDetail));
                        $tipo_costo = $ratedShipmentDetail->ShipmentRateDetail->RateType;
                        switch ($tipo_costo) {
                            case 'PAYOR_LIST_SHIPMENT':
                                $costo = $ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount;
                                //19.92
                                break;
                            case 'PAYOR_ACCOUNT_SHIPMENT':
                                $costo_claro = $ratedShipmentDetail->ShipmentRateDetail->TotalNetCharge->Amount;
                                //19.43
                                break;
                            //PREFERRED_ACCOUNT_SHIPMENT: 369.62
                            //PREFERRED_LIST_SHIPMENT:378.94
                        }
                    }
                }
                if (!property_exists($tarificador, 'servicios')) {
                    $tarificador->servicios = new stdClass();
                }

                $margen = 20;
                $costoAdicional = round(($costo_claro *$margen)/100, 2);
                Log::info(' Costo mensajeria '.$costo_claro);
                Log::info(' Margen: '.$margen);
                Log::info(' Costo adicional internacional '.$costoAdicional);
//                die(print_r($this->porcentaje ));
                $costo_claro = $costo_claro + $costoAdicional;
                Log::info(' Costo internacional '.$costo_claro);

                $valorPaquete = $this->valor_paquete;
                $costoSeguro = 0;
                Log::info(' Costo Serguro '.$costoSeguro);

                $costoTotalCalaculado = round($costo_claro + $costoSeguro, 2);
                Log::info('--Costo total '.$costoTotalCalaculado);
                $servicioMensajeria =  $this->obtenerServicioMensajeria('Fedex',$data->ServiceType);
                $servicio = $this->responseService($costo_claro,$costo_claro,$costoTotalCalaculado, $servicioMensajeria,null,$costoSeguro,true);
                $tarificador->servicios->{$data->ServiceType} = $servicio;
                $tarificador->location  = $this->location;
            }
//            }
            $tarificador->code_response = $this->code_response;
            if (property_exists($tarificador, 'servicios')) {
                return $tarificador;
            }
        }

        $tarificador->success = false;
        $tarificador->codigo = $rateReply->Notifications[0]->Code;
        $tarificador->message = $rateReply->Notifications[0]->Message;
        $tarificador->code_response = $this->code_response;
        return $tarificador;
    }


    public function ratePeticion()
    {
        $rateRequest = new ComplexType\RateRequest();
        //authentication & client details
//        $rateRequest->WebAuthenticationDetail->UserCredential->Key = $this->configuracion->get('key');
//        $rateRequest->WebAuthenticationDetail->UserCredential->Password = $this->configuracion->get('password');
//        $rateRequest->ClientDetail->AccountNumber = $this->configuracion->get('shipAccount');
//        $rateRequest->ClientDetail->MeterNumber = $this->configuracion->get('meter');

        $rateRequest->WebAuthenticationDetail->UserCredential->Key = '1UtuC287BHiT81Ah';
        $rateRequest->WebAuthenticationDetail->UserCredential->Password = 'sMthZiKVv5OzRgHr2ItXW6ZS8';
        $rateRequest->ClientDetail->AccountNumber = '619269314';
        $rateRequest->ClientDetail->MeterNumber = '256798600';
        //    die("<pre>".print_r($rateRequest));
        $rateRequest->ClientDetail->Localization->LocaleCode = 'MX';
        $rateRequest->ClientDetail->Localization->LanguageCode = 'ES';
        $rateRequest->TransactionDetail->CustomerTransactionId = 'testing rate service request';
        //version
        $rateRequest->Version->ServiceId = 'crs';
        $rateRequest->Version->Major = 28;
        $rateRequest->Version->Minor = 0;
        $rateRequest->Version->Intermediate = 0;
        $rateRequest->ReturnTransitAndCommit = true;
        //shipper
        $rateRequest->RequestedShipment->PreferredCurrency = $this->moneda;
        //$rateRequest->RequestedShipment->Shipper->Address->StreetLines = ['10 Fed Ex Pkwy'];
        //$rateRequest->RequestedShipment->Shipper->Address->City = 'Memphis';
        $rateRequest->RequestedShipment->Shipper->Address->StateOrProvinceCode = $this->siglas_codigo_postal_origen;
        $rateRequest->RequestedShipment->Shipper->Address->PostalCode = $this->codigo_postal_origen;
        $rateRequest->RequestedShipment->Shipper->Address->CountryCode = 'MX';
        //recipient
        //$rateRequest->RequestedShipment->Recipient->Address->StreetLines = ['13450 Farmcrest Ct'];
        //$rateRequest->RequestedShipment->Recipient->Address->City = 'Herndon';
        $rateRequest->RequestedShipment->Recipient->Address->StateOrProvinceCode = $this->siglas_codigo_postal_destino;
        $rateRequest->RequestedShipment->Recipient->Address->PostalCode = $this->codigo_postal_destino;
        $rateRequest->RequestedShipment->Recipient->Address->CountryCode = 'MX';
        //shipping charges payment
        $rateRequest->RequestedShipment->ShippingChargesPayment->PaymentType = SimpleType\PaymentType::_SENDER;
        //rate request types
        $rateRequest->RequestedShipment->RateRequestTypes = [
            SimpleType\RateRequestType::_PREFERRED,
            SimpleType\RateRequestType::_LIST
        ];

        $rateRequest->RequestedShipment->PackageCount = 1;

        //create package line items
        $rateRequest->RequestedShipment->RequestedPackageLineItems = [
            new ComplexType\RequestedPackageLineItem()
        ];
        //package 1
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Weight->Value = $this->peso_calculado;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Weight->Units = SimpleType\WeightUnits::_KG;
        //Documento quitar unidades
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Length = $this->largo;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Width = $this->ancho;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Height = $this->alto;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Units = SimpleType\LinearUnits::_CM;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->GroupPackageCount = 1;

        if($this->seguro){
            Log::info("Requiere seguro SI");
            $money = new ComplexType\Money();
            $money->setAmount($this->valor_paquete);
            $money->setCurrency('NMP');
            $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->setInsuredValue($money);
        }
        

        $rateServiceRequest = new RequestFedex();
//        die(print_r($rateRequest));
        Log::info(array($rateRequest));
        $url = 'https://ws.fedex.com:443/web-services/';

//        $url = $this->configuracion->get('location');
        $rateServiceRequest->getSoapClient();
        $rateServiceRequest->getSoapClient()->__setLocation($url.'rate'); //use production URL
        $rateReply = $rateServiceRequest->getGetRatesReply($rateRequest, false); // send true as the 2nd argument to return the SoapClient's stdClass response.
        $request  = $rateServiceRequest->getSoapClient()->__getLastRequest();
        $response = $rateServiceRequest->getSoapClient()->__getLastResponse();

        return [
            'request'=>$request,
            'response'=>$response,
            'rateReply'=>$rateReply
        ];
    }

    public function ratePeticionInternacional($seguro = false)
    {
        Log::info('--Cotizacion Iternacional');
        $rateRequest = new ComplexType\RateRequest();
        //authentication & client details
//        $rateRequest->WebAuthenticationDetail->UserCredential->Key = $this->configuracion->get('key');
//        $rateRequest->WebAuthenticationDetail->UserCredential->Password = $this->configuracion->get('password');
//        $rateRequest->ClientDetail->AccountNumber = $this->configuracion->get('shipAccount');
//        $rateRequest->ClientDetail->MeterNumber = $this->configuracion->get('meter');

        $rateRequest->WebAuthenticationDetail->UserCredential->Key = '1UtuC287BHiT81Ah';
        $rateRequest->WebAuthenticationDetail->UserCredential->Password = 'sMthZiKVv5OzRgHr2ItXW6ZS8';
        $rateRequest->ClientDetail->AccountNumber = '619269314';
        $rateRequest->ClientDetail->MeterNumber = '256798600';
        //    die("<pre>".print_r($rateRequest));
        $rateRequest->ClientDetail->Localization->LocaleCode = 'MX';
        $rateRequest->ClientDetail->Localization->LanguageCode = 'ES';
        $rateRequest->TransactionDetail->CustomerTransactionId = 'T1envios rate internacional';//puede ir cualquier id interno
        //version
        $rateRequest->Version->ServiceId = 'crs';
        $rateRequest->Version->Major = 28;
        $rateRequest->Version->Minor = 0;
        $rateRequest->Version->Intermediate = 0;
        $rateRequest->ReturnTransitAndCommit = true;
//        $rateRequest->ShipTimestamp = '2022-06-29T12:34:56-06:00';

        //RequestedShipment
        $rateRequest->RequestedShipment->DropoffType = 'REGULAR_PICKUP';
        $rateRequest->RequestedShipment->PackagingType = 'YOUR_PACKAGING';

        //RequestedShipment->shipper
        $rateRequest->RequestedShipment->Shipper->Address->StateOrProvinceCode = $this->siglas_codigo_postal_origen;
        $rateRequest->RequestedShipment->Shipper->Address->PostalCode = $this->codigo_postal_origen;
        $rateRequest->RequestedShipment->Shipper->Address->CountryCode = 'MX';

        //recipient
        $rateRequest->RequestedShipment->Recipient->Address->PostalCode = $this->codigo_postal_destino;
        $rateRequest->RequestedShipment->Recipient->Address->CountryCode = $this->pais_destino;

        //shipping charges payment
        $rateRequest->RequestedShipment->ShippingChargesPayment->PaymentType = SimpleType\PaymentType::_SENDER;
//        $rateRequest->RequestedShipment->ShippingChargesPayment->Payor->ResponsibleParty->AccountNumber = $this->configuracion->get('shipAccount');
        $rateRequest->RequestedShipment->ShippingChargesPayment->Payor->ResponsibleParty->AccountNumber = '619269314';


        //rate request types
        $rateRequest->RequestedShipment->RateRequestTypes = ['LIST'];

        $rateRequest->RequestedShipment->PackageCount = 1;

        //create package line items
        $rateRequest->RequestedShipment->RequestedPackageLineItems = [
            new ComplexType\RequestedPackageLineItem()
        ];

        //package 1
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Weight->Value = $this->peso;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Weight->Units = SimpleType\WeightUnits::_KG;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->SequenceNumber = 1;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->GroupNumber = 1;

        $money = new ComplexType\Money();
        $money->setAmount($this->valor_paquete);
        $money->setCurrency($this->moneda);//NO PERMITE MXN

        //Documento quitar unidades
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Length = $this->largo;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Width = $this->ancho;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Height = $this->alto;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->Dimensions->Units = SimpleType\LinearUnits::_CM;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->GroupPackageCount = 1;
        $rateRequest->RequestedShipment->RequestedPackageLineItems[0]->setInsuredValue($money);

        $rateServiceRequest = new RequestFedex();
        Log::info(array($rateRequest));
        $rateServiceRequest->getSoapClient();
        $url = 'https://ws.fedex.com:443/web-services/';
//        $url = $this->configuracion->get('location');
        $rateServiceRequest->getSoapClient()->__setLocation($url.'rate'); //use production URL
        $rateReply = $rateServiceRequest->getGetRatesReply($rateRequest, false); // send true as the 2nd argument to return the SoapClient's stdClass response.
        $request  = $rateServiceRequest->getSoapClient()->__getLastRequest();
        $response = $rateServiceRequest->getSoapClient()->__getLastResponse();
//        die(print_r($request));

        return [
            'request'=>$request,
            'response'=>$response,
            'rateReply'=>$rateReply
        ];
    }

    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $result = $this->configuracionGenerarGuia($guiaMensajeriaTO);
//        die(print_r($result));
        if ($result->HighestSeverity == 'ERROR' || $result->HighestSeverity == 'FAILURE') {
            $notification = $result->Notifications;

            Log::info('Problema al generar Guia FEDEX: ');
//            Log::info(json_encode($notification));
            if(is_object($notification)){
                $error = $notification;
            }else{
                $error = $notification[0];
            }
            throw new ValidacionException($error->Code.' '.$error->Message);
        }
        $guia = $result->CompletedShipmentDetail->MasterTrackingId->TrackingNumber;
        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath.("/".$guia.'_'.date('YmdHis').'.'.$this->extension_guia_impresion);
        $guiaMensajeriaTO->setRutaArchivo($rutaArchivo);
        Log::info('archivo: '.$rutaArchivo);
        $imagen = $result->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
//       die(print_r($this->numero_externo));

        $nombreArchivo = $guia.'_'.date('YmdHis').'.pdf';


        //Modifica pdf
        $ZPL = new ZPL();
        $cadenaFinal = $ZPL->contenidoFedex($imagen,$guiaMensajeriaTO, $this->numero_externo, $guia);

        //Crear ZPL
        $archivoZPL= $ZPL->convertirZPL($cadenaFinal,'pdf','');
//        $archivoZPL= ['success'=>false];

        if($archivoZPL['success'] == true || $archivoZPL['success'] == 1) {
            $archivoGuia = $archivoZPL['data'];
        }else{
            Log::info("Fallo zpl, se genera pdf normal");
            $result = $this->configuracionGenerarGuia($guiaMensajeriaTO,'pdf');
            $archivoGuia = $result->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
        }

        file_put_contents($rutaArchivo, $archivoGuia);
//        die(print_r($guiaMensajeriaTO->getCodificacion() ));
        $dataFile = $guiaMensajeriaTO->getCodificacion() == 'utf8' ? utf8_encode($archivoGuia) : base64_encode($archivoGuia);

        $array = [
            'guia'=>$result->CompletedShipmentDetail->MasterTrackingId->TrackingNumber,
            'location'=>$result->location,
            'imagen'=>$dataFile,
            'extension'=>$this->extension_guia_impresion,
            'nombreArchivo' => $nombreArchivo,
            'ruta' => $rutaArchivo,
            'link_rastreo_entrega'=> env('TRACKING_LINK_T1ENVIOS')."".$guia,
            'infoExtra'=>[
                'codigo'=>'OC',
                'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
                'identificadorUnico'=>'',
                'tracking_link' =>env('TRACKING_LINK_T1ENVIOS')."".$guia
                // 'tracking_link' =>'https://www.fedex.com/apps/fedextrack/?tracknumbers='.$result->CompletedShipmentDetail->MasterTrackingId->TrackingNumber
            ]
        ];
//
        if ($guiaMensajeriaTO->getGenerarRecoleccion()) {
            Log::info('Recoleccion FEDEX');
            $recoleccion = $this->recoleccion($guiaMensajeriaTO);
            $array['recoleccion'] = $recoleccion;
        }

        return $array;
    }

    public function generarGuiaInternacional(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $result = $this->configuracionGenerarGuiaInternacional($guiaMensajeriaTO);
//        die(print_r($result));
        if ($result->HighestSeverity == 'ERROR' || $result->HighestSeverity == 'FAILURE') {
            $notification = $result->Notifications;

            Log::info('Problema al generar Guia FEDEX: ');
            Log::info(json_encode($notification));
            if(is_object($notification)){
                $error = $notification;
            }else{
                $error = $notification[0];
            }
            throw new ValidacionException($error->Code.' '.$error->Message);
        }
        $tmpPath = sys_get_temp_dir();
        $rutaArchivo = $tmpPath.('/guia_'.date('YmdHis').'.'.$this->extension_guia_impresion);
        $guiaMensajeriaTO->setRutaArchivo($rutaArchivo);
        Log::info('archivo: '.$rutaArchivo);
        $imagen = $result->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
//       die(print_r($this->numero_externo));

        $guia = $result->CompletedShipmentDetail->MasterTrackingId->TrackingNumber;
        $nombreArchivo = $guia.'_'.date('YmdHis').'.pdf';

        //Modifica pdf
        $ZPL = new ZPL();
        $cadenaFinal = $ZPL->contenidoFedexInternacional($imagen,$guiaMensajeriaTO, $this->numero_externo);

        //Crear ZPL
        $archivoZPL= $ZPL->convertirZPL($cadenaFinal,'pdf','');
//        $archivoZPL= ['success'=>false];

        if($archivoZPL['success'] == true || $archivoZPL['success'] == 1) {
            $archivoGuia = $archivoZPL['data'];
        }else{
            Log::info("Fallo zpl, se genera pdf normal");
            $result = $this->configuracionGenerarGuia($guiaMensajeriaTO,'pdf');
            $archivoGuia = $result->CompletedShipmentDetail->CompletedPackageDetails->Label->Parts->Image;
        }

        $archivoFactura = $result->CompletedShipmentDetail->ShipmentDocuments->Parts->Image;
        $rutaFactura = $tmpPath.('/factura_'.$guia.'_'.date('YmdHis').'.pdf');
        $nombreFactura  = 'factura_'.$guia.'.pdf';

//        die(print_r($archivoFactura));

        file_put_contents($rutaArchivo, $archivoGuia);
        file_put_contents($rutaFactura, $archivoFactura);
        $array = [
            'guia'=>$result->CompletedShipmentDetail->MasterTrackingId->TrackingNumber,
            'location'=>$result->location,
            'imagen'=>utf8_encode($archivoGuia),
            'nombreArchivo' => $nombreArchivo,
            'ruta' => $rutaArchivo,
            'extension'=>$this->extension_guia_impresion,
            'imagenInvoice'=>utf8_encode($archivoFactura),
            'nombreFactura' => $nombreFactura,
            'rutaFactura' => $rutaFactura,
            'infoExtra'=>[
                'codigo'=>'OC',
                'fecha_hora'=>Carbon::now()->format('Y-m-d H:i:s'),
                'identificadorUnico'=>'',
                'tracking_link' =>'https://www.fedex.com/apps/fedextrack/?tracknumbers='.$result->CompletedShipmentDetail->MasterTrackingId->TrackingNumber
            ]
        ];
//
        if ($guiaMensajeriaTO->getGenerarRecoleccion()) {
            Log::info('Recoleccion FEDEX');
            $recoleccion = $this->recoleccion($guiaMensajeriaTO);
            $array['recoleccion'] = $recoleccion;
        }

        return $array;
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

    private function configuracionGenerarGuiainternacional(GuiaMensajeriaTO $guiaMensajeriaTO, $formato = "zpl")
    {
        $bitacoraMensajeriaDestinoTO = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $comercio = Comercio::where('id',$guiaMensajeriaTO->getComercioId())->first();
        $this->code_response = 200;

        if (($bitacoraMensajeriaDestinoTO instanceof BitacoraMensajeriaDestinoTO)
            && ($bitacoraMensajeriaOrigenTO instanceof BitacoraMensajeriaOrigenTO)
            && ($bitacoraCotizacionMensajeriaTO instanceof BitacoraCotizacionMensajeriaTO)
        ) {
            $fecha = $bitacoraCotizacionMensajeriaTO->getFechaLiberacion();
            $userCredential = new ShipService\ComplexType\WebAuthenticationCredential();
            $userCredential->setKey($this->configuracion->get('key'))
                ->setPassword($this->configuracion->get('password'));

            $webAuthenticationDetail = new ShipService\ComplexType\WebAuthenticationDetail();
//            die("<pre>".print_r($comercio, true));
            $webAuthenticationDetail->setUserCredential($userCredential);
            $clientDetail = new ShipService\ComplexType\ClientDetail();
            $clientDetail
                ->setAccountNumber($this->configuracion->get('shipAccount'))
                ->setMeterNumber($this->configuracion->get('meter'));

            $zpl = new ZPL();
            $version = new ShipService\ComplexType\VersionId();
            $version
                ->setMajor(26)
                ->setIntermediate(0)
                ->setMinor(0)
                ->setServiceId('ship');
            $shipperAddress = new ShipService\ComplexType\Address();
            $shipperAddress
                ->setStreetLines([$zpl->removerAcentos($bitacoraMensajeriaOrigenTO->getDireccionCompuesta().' '.$bitacoraMensajeriaOrigenTO->getColonia())])
                ->setCity($zpl->removerAcentos($bitacoraMensajeriaOrigenTO->getMunicipio()))
                ->setStateOrProvinceCode($this->siglas_codigo_postal_origen)
                ->setPostalCode($bitacoraCotizacionMensajeriaTO->getCodigoPostalOrigen())
                ->setCountryCode('MX');
            $shipperContact = new ShipService\ComplexType\Contact();

            $nombreOrigen = $zpl->removerAcentos($bitacoraMensajeriaOrigenTO->getNombre() . ' ' . $bitacoraMensajeriaOrigenTO->getApellidos());
            $shipperContact
                ->setCompanyName($comercio->descripcion)
                ->setEMailAddress($bitacoraMensajeriaOrigenTO->getEmail())
                ->setPersonName($nombreOrigen)
                ->setPhoneNumber($bitacoraMensajeriaOrigenTO->getTelefono());
            $shipper = new ShipService\ComplexType\Party();
            $shipper
                ->setAccountNumber($this->configuracion->get('shipAccount'))
                ->setAddress($shipperAddress)
                ->setContact($shipperContact);
            $recipientAddress = new ShipService\ComplexType\Address();
            $recipientAddress
                ->setStreetLines([$bitacoraMensajeriaDestinoTO->getDireccionCompuesta().' '.$bitacoraMensajeriaDestinoTO->getColonia()])
                ->setCity($bitacoraMensajeriaDestinoTO->getMunicipio())
                ->setStateOrProvinceCode($this->codigo_estado)
                ->setPostalCode($bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino())
                ->setCountryCode($this->pais_destino)
                ->setResidential(false);

            $nombreDestino = $zpl->removerAcentos($bitacoraMensajeriaDestinoTO->getNombre() . ' ' . $bitacoraMensajeriaDestinoTO->getApellidos());
            $recipientContact = new ShipService\ComplexType\Contact();
            $recipientContact
                ->setPersonName($nombreDestino)
                ->setPhoneNumber($bitacoraMensajeriaDestinoTO->getTelefono());
            $recipient = new ShipService\ComplexType\Party();
            $recipient
                ->setAddress($recipientAddress)
                ->setContact($recipientContact);
            $labelSpecification = new ShipService\ComplexType\LabelSpecification();

            if($formato == "zpl") {

                $customerSpecifiedDetail = new ShipService\ComplexType\CustomerSpecifiedLabelDetail();
                $customerSpecifiedDetail->setMaskedData(['TRANSPORTATION_CHARGES_PAYOR_ACCOUNT_NUMBER','DUTIES_AND_TAXES_PAYOR_ACCOUNT_NUMBER']);//PREGUNTAR QUE VA AQUI

                $labelSpecification
                    ->setLabelStockType(new SimpleType\LabelStockType(SimpleType\LabelStockType::_STOCK_4X6))
                    ->setImageType(new SimpleType\ShippingDocumentImageType(SimpleType\ShippingDocumentImageType::_ZPLII))
                    ->setLabelFormatType(new SimpleType\LabelFormatType(SimpleType\LabelFormatType::_COMMON2D))
                    ->setLabelPrintingOrientation(ShipService\SimpleType\LabelPrintingOrientationType::_BOTTOM_EDGE_OF_TEXT_FIRST)
                    ->setCustomerSpecifiedDetail($customerSpecifiedDetail);
            }elseif($formato == "pdf"){
                $labelSpecification
                    ->setLabelStockType(new ShipService\SimpleType\LabelStockType(ShipService\SimpleType\LabelStockType::_PAPER_7X4POINT75))
                    ->setImageType(new ShipService\SimpleType\ShippingDocumentImageType(ShipService\SimpleType\ShippingDocumentImageType::_PDF))
//                    ->setImageType(new ShipService\SimpleType\ShippingDocumentImageType($this->formato_guia_impresion))
                    ->setLabelFormatType(new ShipService\SimpleType\LabelFormatType(ShipService\SimpleType\LabelFormatType::_COMMON2D));
            }elseif($formato == "png"){
                $labelSpecification
                    ->setLabelStockType(new SimpleType\LabelStockType(SimpleType\LabelStockType::_PAPER_4X6))
                    ->setImageType(new SimpleType\ShippingDocumentImageType(SimpleType\ShippingDocumentImageType::_PNG))
                    ->setLabelFormatType(new SimpleType\LabelFormatType(SimpleType\LabelFormatType::_COMMON2D));
            }

            $shippingDocumentSpecification = new ShipService\ComplexType\ShippingDocumentSpecification();
            $shippingDocumentSpecification->setShippingDocumentTypes(['COMMERCIAL_INVOICE']);

            $commercialInvoiceDetail = new ShipService\ComplexType\CommercialInvoiceDetail();

            $format = new ShipService\ComplexType\ShippingDocumentFormat();
            $format->setImageType('PDF');
            $format->setStockType('PAPER_LETTER');
            $format->setProvideInstructions(1);

            $commercialInvoiceDetail->setFormat($format);
            $shippingDocumentSpecification->setCommercialInvoiceDetail($commercialInvoiceDetail);


            $packageLineItem1 = new ShipService\ComplexType\RequestedPackageLineItem();
            $packageLineItem1
                ->setSequenceNumber(1)
                ->setItemDescription($guiaMensajeriaTO->getContenido())
                ->setDimensions(new ShipService\ComplexType\Dimensions(array(
                    'Width' => $bitacoraCotizacionMensajeriaTO->getAncho(),
                    'Height' => $bitacoraCotizacionMensajeriaTO->getAlto(),
                    'Length' => $bitacoraCotizacionMensajeriaTO->getLargo(),
                    'Units' => SimpleType\LinearUnits::_CM
                )))
                ->setWeight(new ShipService\ComplexType\Weight(array(
                    'Value' => $bitacoraCotizacionMensajeriaTO->getPeso(),
                    'Units' => SimpleType\WeightUnits::_KG
                )));
            $shippingChargesPayor = new ShipService\ComplexType\Payor();
            $shippingChargesPayor->setResponsibleParty($shipper);

            $shippingChargesPayment = new ShipService\ComplexType\Payment();
            $shippingChargesPayment
                ->setPaymentType(ShipService\SimpleType\PaymentType::_SENDER)
                ->setPayor($shippingChargesPayor);
            $requestedShipment = new ShipService\ComplexType\RequestedShipment();
            $requestedShipment->setShipTimestamp($fecha->format('c'));
            //$requestedShipment->setShipTimestamp(date('c'));
            $requestedShipment->setDropoffType(
                new ShipService\SimpleType\DropoffType(ShipService\SimpleType\DropoffType::_REGULAR_PICKUP)
            );

            $money = new ShipService\ComplexType\Money();
            $money->setCurrency($bitacoraCotizacionMensajeriaTO->getMoneda());
            $money->setAmount($bitacoraCotizacionMensajeriaTO->getValorPaquete());

            $dutiesPayment = new ShipService\ComplexType\Payment();
            $dutiesPayment->setPaymentType(ShipService\SimpleType\PaymentType::_SENDER);
            $dutiesPaymentPayor = new ShipService\ComplexType\Payor();

            $payor = new ShipService\ComplexType\Party();
            $payor->setAccountNumber($this->configuracion->get('shipAccount'));

            $dutiesPaymentPayor->setResponsibleParty($payor);
            $dutiesPayment->setPayor($dutiesPaymentPayor);


            $customsClearanceDetail = new ShipService\ComplexType\CustomsClearanceDetail();
            $customsClearanceDetail->setDutiesPayment($dutiesPayment);
            $customsClearanceDetail->setDocumentContent('NON_DOCUMENTS');//Cambia cuando se envian documentos
            $customsClearanceDetail->setCustomsValue($money);//NO ACEPTA MXN

            //En espera de que indiquen donde va
//            $comodity = new ShipService\ComplexType\Commodity();
//            $comodity->setPurpose(ShipService\SimpleType\PurposeOfShipmentType::_SOLD);
//            $purposeOfShipmentType = new ShipService\SimpleType\PurposeOfShipmentType();

            $commodities = [
                'Name' => $guiaMensajeriaTO->getContenido(),
                'NumberOfPieces' =>1,
                'Description' =>$guiaMensajeriaTO->getContenido(),
                'CountryOfManufacture' =>$this->pais_fabricacion,
                'HarmonizedCode' => $this->categoria,
                'Weight' => [
                    'Units' =>'KG',
                    'Value' =>$this->peso,
                ],
                'Quantity' =>1,
                'QuantityUnits' =>'EA', ///--->Significa cada uno, por pieza
                'UnitPrice' =>[
                    'Currency' => $bitacoraCotizacionMensajeriaTO->getMoneda(),
                    'Amount' => $bitacoraCotizacionMensajeriaTO->getValorPaquete(),
                ]
            ];

//            die(print_r($commodities));
            $customsClearanceDetail->setCommodities($commodities);

            $requestedShipment->setServiceType(new ShipService\SimpleType\ServiceType($bitacoraCotizacionMensajeriaTO->getTipoServicio()));
            $requestedShipment->setPackagingType(new ShipService\SimpleType\PackagingType(ShipService\SimpleType\PackagingType::_YOUR_PACKAGING));
            $requestedShipment->setShipper($shipper);
            $requestedShipment->setRecipient($recipient);
            $requestedShipment->setLabelSpecification($labelSpecification);
            $requestedShipment->setShippingDocumentSpecification($shippingDocumentSpecification);
            $requestedShipment->setRateRequestTypes(array(new ShipService\SimpleType\RateRequestType(ShipService\SimpleType\RateRequestType::_LIST)));
            $requestedShipment->setPackageCount(1);
            $requestedShipment->setRequestedPackageLineItems([
                $packageLineItem1
            ]);
            $requestedShipment->setCustomsClearanceDetail($customsClearanceDetail);

            $requestedShipment->setShippingChargesPayment($shippingChargesPayment);
            $processShipmentRequest = new ShipService\ComplexType\ProcessShipmentRequest();
            $processShipmentRequest->setWebAuthenticationDetail($webAuthenticationDetail);
            $processShipmentRequest->setClientDetail($clientDetail);
            $processShipmentRequest->setVersion($version);
            $processShipmentRequest->setRequestedShipment($requestedShipment);
            $shipService = new ShipService\Request();
            $shipService->getSoapClient()->__setLocation($this->configuracion->get('location').'ship');
            Log::info(array($processShipmentRequest));
//            die(print_r($processShipmentRequest));
            $result = $shipService->getProcessShipmentReply($processShipmentRequest, true);

            $this->code_response = $result->HighestSeverity == 'SUCCESS'?200:400;
            $result->location = $this->location;
            $this->request = $shipService->getSoapClient()->__getLastRequest();
            $this->response = $shipService->getSoapClient()->__getLastResponse();

//            die(print_r($this->response ));

            return $result;
        }


    }

    private function configuracionGenerarGuia(GuiaMensajeriaTO $guiaMensajeriaTO, $formato = "zpl")
    {

        $bitacoraMensajeriaDestinoTO = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $comercio = Comercio::where('id', $guiaMensajeriaTO->getComercioId())->first();
        $this->code_response = 200;

        if (($bitacoraMensajeriaDestinoTO instanceof BitacoraMensajeriaDestinoTO)
            && ($bitacoraMensajeriaOrigenTO instanceof BitacoraMensajeriaOrigenTO)
            && ($bitacoraCotizacionMensajeriaTO instanceof BitacoraCotizacionMensajeriaTO)
        ) {
            $fecha = $bitacoraCotizacionMensajeriaTO->getFechaLiberacion();
            $userCredential = new ShipService\ComplexType\WebAuthenticationCredential();
            $userCredential->setKey($this->configuracion->get('key'))
                ->setPassword($this->configuracion->get('password'));

            $webAuthenticationDetail = new ShipService\ComplexType\WebAuthenticationDetail();
//            die("<pre>".print_r($comercio, true));
            $webAuthenticationDetail->setUserCredential($userCredential);
            $clientDetail = new ShipService\ComplexType\ClientDetail();
            $clientDetail
                ->setAccountNumber($this->configuracion->get('shipAccount'))
                ->setMeterNumber($this->configuracion->get('meter'));

            $version = new ShipService\ComplexType\VersionId();
            $version
                ->setMajor(26)
                ->setIntermediate(0)
                ->setMinor(0)
                ->setServiceId('ship');
            $shipperAddress = new ShipService\ComplexType\Address();
            $shipperAddress
                ->setStreetLines([$bitacoraMensajeriaOrigenTO->getDireccionCompuesta() . ' ' . $bitacoraMensajeriaOrigenTO->getColonia()])
                ->setCity($bitacoraMensajeriaOrigenTO->getMunicipio())
                ->setStateOrProvinceCode($this->siglas_codigo_postal_origen)
                ->setPostalCode($bitacoraCotizacionMensajeriaTO->getCodigoPostalOrigen())
                ->setCountryCode('MX');
            $shipperContact = new ShipService\ComplexType\Contact();
            $shipperContact
                ->setCompanyName($comercio->descripcion)
                ->setEMailAddress($bitacoraMensajeriaOrigenTO->getEmail())
                ->setPersonName($bitacoraMensajeriaOrigenTO->getNombre() . ' ' . $bitacoraMensajeriaOrigenTO->getApellidos())
                ->setPhoneNumber($bitacoraMensajeriaOrigenTO->getTelefono());
            $shipper = new ShipService\ComplexType\Party();
            $shipper
                ->setAccountNumber($this->configuracion->get('shipAccount'))
                ->setAddress($shipperAddress)
                ->setContact($shipperContact);
            $recipientAddress = new ShipService\ComplexType\Address();
            $recipientAddress
                ->setStreetLines([$bitacoraMensajeriaDestinoTO->getDireccionCompuesta() . ' ' . $bitacoraMensajeriaDestinoTO->getColonia()])
                ->setCity($bitacoraMensajeriaDestinoTO->getMunicipio())
                ->setStateOrProvinceCode($this->siglas_codigo_postal_destino)
                ->setPostalCode($bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino())
                ->setCountryCode('MX');
            $recipientContact = new ShipService\ComplexType\Contact();
            $recipientContact
                ->setPersonName($bitacoraMensajeriaDestinoTO->getNombre() . ' ' . $bitacoraMensajeriaDestinoTO->getApellidos())
                ->setPhoneNumber($bitacoraMensajeriaDestinoTO->getTelefono());
            $recipient = new ShipService\ComplexType\Party();
            $recipient
                ->setAddress($recipientAddress)
                ->setContact($recipientContact);
            $labelSpecification = new ShipService\ComplexType\LabelSpecification();

            if ($formato == "zpl") {
                $labelSpecification
                    ->setLabelStockType(new SimpleType\LabelStockType(SimpleType\LabelStockType::_STOCK_4X6POINT75_LEADING_DOC_TAB))
                    ->setImageType(new SimpleType\ShippingDocumentImageType(SimpleType\ShippingDocumentImageType::_ZPLII))
                    ->setLabelFormatType(new SimpleType\LabelFormatType(SimpleType\LabelFormatType::_COMMON2D));
            } elseif ($formato == "pdf") {
                $labelSpecification
                    ->setLabelStockType(new ShipService\SimpleType\LabelStockType(ShipService\SimpleType\LabelStockType::_PAPER_7X4POINT75))
                    ->setImageType(new ShipService\SimpleType\ShippingDocumentImageType(ShipService\SimpleType\ShippingDocumentImageType::_PDF))
//                    ->setImageType(new ShipService\SimpleType\ShippingDocumentImageType($this->formato_guia_impresion))
                    ->setLabelFormatType(new ShipService\SimpleType\LabelFormatType(ShipService\SimpleType\LabelFormatType::_COMMON2D));
            } elseif ($formato == "png") {
                $labelSpecification
                    ->setLabelStockType(new SimpleType\LabelStockType(SimpleType\LabelStockType::_PAPER_4X6))
                    ->setImageType(new SimpleType\ShippingDocumentImageType(SimpleType\ShippingDocumentImageType::_PNG))
                    ->setLabelFormatType(new SimpleType\LabelFormatType(SimpleType\LabelFormatType::_COMMON2D));
            }

            $packageLineItem1 = new ShipService\ComplexType\RequestedPackageLineItem();
            $packageLineItem1
                ->setSequenceNumber(1)
                ->setItemDescription($guiaMensajeriaTO->getContenido())
                ->setDimensions(new ShipService\ComplexType\Dimensions(array(
                    'Width' => $bitacoraCotizacionMensajeriaTO->getAncho(),
                    'Height' => $bitacoraCotizacionMensajeriaTO->getAlto(),
                    'Length' => $bitacoraCotizacionMensajeriaTO->getLargo(),
                    'Units' => SimpleType\LinearUnits::_CM
                )))
                ->setWeight(new ShipService\ComplexType\Weight(array(
                    'Value' => $bitacoraCotizacionMensajeriaTO->getPeso(),
                    'Units' => SimpleType\WeightUnits::_KG
                )));


            $shippingChargesPayor = new ShipService\ComplexType\Payor();
            $shippingChargesPayor->setResponsibleParty($shipper);
            $shippingChargesPayment = new ShipService\ComplexType\Payment();
            $shippingChargesPayment
                ->setPaymentType(ShipService\SimpleType\PaymentType::_SENDER)
                ->setPayor($shippingChargesPayor);

            $requestedShipment = new ShipService\ComplexType\RequestedShipment();
            $requestedShipment->setShipTimestamp($fecha->format('c'));
            //$requestedShipment->setShipTimestamp(date('c'));
            $requestedShipment->setDropoffType(
                new ShipService\SimpleType\DropoffType(ShipService\SimpleType\DropoffType::_REGULAR_PICKUP)
            );
            $requestedShipment->setServiceType(new ShipService\SimpleType\ServiceType($bitacoraCotizacionMensajeriaTO->getTipoServicio()));
            $requestedShipment->setPackagingType(new ShipService\SimpleType\PackagingType(ShipService\SimpleType\PackagingType::_YOUR_PACKAGING));
            $requestedShipment->setShipper($shipper);
            $requestedShipment->setRecipient($recipient);
            $requestedShipment->setLabelSpecification($labelSpecification);
            $requestedShipment->setRateRequestTypes(array(new ShipService\SimpleType\RateRequestType(ShipService\SimpleType\RateRequestType::_PREFERRED)));
            $requestedShipment->setPackageCount(1);
            $requestedShipment->setRequestedPackageLineItems([
                $packageLineItem1
            ]);

            if($bitacoraCotizacionMensajeriaTO->getSeguro()){
//                die(print_r());

                Log::info("Requiere seguro SI");
                $packageLineItem1->setInsuredValue(new ShipService\ComplexType\Money(
                    array(
                        'Currency' => 'NMP',
                        'Amount' => number_format($bitacoraCotizacionMensajeriaTO->getValorPaquete(), 2,'.', '')
                    )));
//                $money = new ComplexType\Money();
//                $money->setAmount($this->valor_paquete);
//                $money->setCurrency('NMP');
//                $requestedShipment->set($money);
            }

            $requestedShipment->setShippingChargesPayment($shippingChargesPayment);
            $processShipmentRequest = new ShipService\ComplexType\ProcessShipmentRequest();
            $processShipmentRequest->setWebAuthenticationDetail($webAuthenticationDetail);
            $processShipmentRequest->setClientDetail($clientDetail);
            $processShipmentRequest->setVersion($version);
            $processShipmentRequest->setRequestedShipment($requestedShipment);
            $shipService = new ShipService\Request();
            $shipService->getSoapClient()->__setLocation($this->configuracion->get('location') . 'ship');
            Log::info(array($processShipmentRequest));
            $result = $shipService->getProcessShipmentReply($processShipmentRequest, true);

            $this->code_response = $result->HighestSeverity == 'SUCCESS' ? 200 : 400;
            $result->location = $this->location;
            $this->request = $shipService->getSoapClient()->__getLastRequest();
            $this->response = $shipService->getSoapClient()->__getLastResponse();

            return $result;
        }
    }



    /**
     * @return ResponseTrack
     * @throws ValidacionException
     */
    public function rastreoGuia()
    {
        $bitacoraMensajeriaDestino = $this->getGuiaMensajeria()->bitacoraMensajeriaDestino;
        $bitacoraMensajeriaOrigen = $this->getGuiaMensajeria()->bitacoraMensajeriaOrigen;

        $response = $this->configuracionRastreoGuia();
        $rastreo = $this->setDatosResponseWebService($response);
        $rastreo->location = $this->location;
        $rastreo->ubicacion_origen = $bitacoraMensajeriaOrigen->estado;
        $rastreo->ubicacion_destino = $bitacoraMensajeriaDestino->estado;

        $this->verificarExcedente($response);

        $responseTrack = New ResponseTrack();
        $responseTrack->setRequest($this->getRequest());
        $responseTrack->setResponse($this->getResponse());
        $responseTrack->setTrack($rastreo);

        //busca track en db
        $encontrarTrack = TrackMensajeriaResponse::where('guia_mensajeria_id', $this->getGuiaMensajeria()->id)->get();

        if($encontrarTrack->count() > 0){
            $ultimoTrack = $encontrarTrack->last();
            $ultimoTrackResponse = '<?xml version="1.0" encoding="UTF-8"?>'.$ultimoTrack['response'];

            $ultimoXmlResponse = new \DOMDocument();
            $ultimoXmlResponse->preserveWhiteSpace = FALSE;
            $ultimoXmlResponse->loadXML($ultimoTrackResponse);
            $ultimosEventos = $ultimoXmlResponse->getElementsByTagName('Events')->length;

            if(count($responseTrack->getTrack()->eventos) == $ultimosEventos){
                $responseTrack->setActualiza(false);
            }
        }

        return $responseTrack;
    }

    private function configuracionRastreoGuia($arrayGuias = [])
    {
        if (count($arrayGuias) == 0) {
            $arrayGuias[] = $this->guia_mensajeria->guia;
        }
        $trackRequest = new TrackService\ComplexType\TrackRequest();

        // User Credential
        $trackRequest->WebAuthenticationDetail->UserCredential->Key = $this->configuracion->get('key');
        $trackRequest->WebAuthenticationDetail->UserCredential->Password = $this->configuracion->get('password');
        $trackRequest->ClientDetail->AccountNumber = $this->configuracion->get('shipAccount');
        $trackRequest->ClientDetail->MeterNumber = $this->configuracion->get('meter');
        $trackRequest->ClientDetail->Localization->LocaleCode = 'MX';
        $trackRequest->ClientDetail->Localization->LanguageCode = 'ES';
        // Version
        $trackRequest->Version->ServiceId = 'trck';
        $trackRequest->Version->Major = 16;
        $trackRequest->Version->Intermediate = 0;
        $trackRequest->Version->Minor = 0;


        $trackRequest->TransactionDetail->Localization->LocaleCode = 'US';
        $trackRequest->TransactionDetail->Localization->LanguageCode = 'ES';
        $trackRequest->ProcessingOptions = ['INCLUDE_DETAILED_SCANS'];

        // Track shipment 1
        $arraySelectionDetails = [];
        foreach ($arrayGuias as $key=>$guia) {
            $trackSelectionDetail = new TrackService\ComplexType\TrackSelectionDetail();
            $trackSelectionDetail->PackageIdentifier->Value = $guia;
            $trackSelectionDetail->PackageIdentifier->Type
                = TrackService\SimpleType\TrackIdentifierType::_TRACKING_NUMBER_OR_DOORTAG;
            $arraySelectionDetails[] = $trackSelectionDetail;
        }
        $trackRequest->SelectionDetails = $arraySelectionDetails;

        $request = new TrackService\Request();
        $request->getSoapClient()->__setLocation($this->configuracion->get('location').'track');
        $trackReply = $request->getTrackReply($trackRequest, true);

        self::setRequest($request->getSoapClient()->__getLastRequest());
        self::setResponse($request->getSoapClient()->__getLastResponse());
        return $trackReply;
    }

    public function verificarExcedente($response){
//        die(print_r($response));

        $xmlResponse = new \DOMDocument();
        $xmlResponse->preserveWhiteSpace = FALSE;
        $xmlResponse->loadXML($this->getResponse());
        $codes = $xmlResponse->getElementsByTagName('Code');
        $entregado = false;
        foreach ($codes as $code){
            if($code->nodeValue == 'DL') {
                $entregado = true;
                break;
            }

        }

        if(!$entregado){
            $pesoPaquete = $response->CompletedTrackDetails->TrackDetails[0]->PackageWeight;
            //buscar si existe un excedente
            $excedente = GuiaMensajeria::select('guias_mensajerias.*',DB::raw('guias_excedentes.id as guia_excedente_id'))
                ->leftjoin('guias_excedentes','guias_mensajerias.guia','=','guias_excedentes.guia')
                ->where('guias_mensajerias.guia', $this->getGuiaMensajeria()->guia)
                ->where('guias_mensajerias.status_entrega','!=',GuiaMensajeria::$status['entregada'])
                ->get()->last();
            $excedentePeso = 0;
            if(!$excedente->guia_excedente_id){
                $bitacoraCotizacion = BitacoraCotizacionMensajeria::findOrFail($excedente->bitacora_cotizacion_mensajeria_id);
                //  die("<pre>".print_r( $excedente->toArray()));

                $pesoTrack = $pesoPaquete->Value;
                if($pesoPaquete->Units != 'KG'){
                    $pesoTrack = conversionKilogramos($pesoPaquete->Units, $pesoPaquete->Value);
                }

                $pesoTrack = 20;
                if($pesoTrack > $bitacoraCotizacion->peso){
                    $excedentePeso = $pesoTrack - $bitacoraCotizacion->peso;
                }

                if($excedentePeso > 0){
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
        $response = $this->configuracionRastreoGuia($arrayGuias);
        $arrayRastreo = $this->setDatosResponseWebService($response, false);
        return $arrayRastreo;
    }

    private function setDatosResponseWebService($response, $first = true)
    {

        if($response->HighestSeverity == 'ERROR'){
            throw new ValidacionException($response->Notifications->Code.' '.
                $response->Notifications->Message.' '.$this->configuracion->get('location'));
        }
        $arrayRastreos = [];
        $completedTrackDetails = $response->CompletedTrackDetails;

        if (!is_array($completedTrackDetails)) {
            $completedTrackDetails = [$response->CompletedTrackDetails];
        }

        foreach ($completedTrackDetails as $completedTrackDetail) {

            if (!is_array($completedTrackDetail->TrackDetails)) {
                $completedTrackDetail->TrackDetails = [$completedTrackDetail->TrackDetails];
            }
            $arrayEventos = [];
            foreach ($completedTrackDetail->TrackDetails as $trackDetail) {
                $eventsTrack = [];
                $statusEntrega = 1;
                $notificacion = $trackDetail->Notification;
                $rastreo = new stdClass();
                $rastreo->status = $notificacion->Severity;
                $rastreo->codigo_ubicacion_origen = $this->siglas_codigo_postal_origen;
                //$rastreo->ubicacion_origen = $bitacoraMensajeriaOrigen->estado;
                $rastreo->codigo_ubicacion_destino = $this->siglas_codigo_postal_destino;
                //$rastreo->ubicacion_destino = $bitacoraMensajeriaDestino->estado;
                $rastreo->guia = $trackDetail->TrackingNumber;

                if ($trackDetail->Notification->Severity == 'ERROR') {
                    if ($first) {
                        throw new ValidacionException(
                            $trackDetail->Notification->Code.' '.
                            $trackDetail->Notification->LocalizedMessage
                        );
                    }
                    $rastreo->status_entrega = $statusEntrega;
                    $rastreo->mensaje = $notificacion->Message;
                    $rastreo->eventos = $arrayEventos;
                    continue;
                }

                if (property_exists($trackDetail, 'Events')) {
                    if (!is_array($trackDetail->Events)) {
                        $trackDetail->Events = [$trackDetail->Events];
                    }

                    $eventsTrack = $trackDetail->Events;
                }
                foreach ($eventsTrack as $event) {
                    $direccion = $event->Address;
                    $timestamps = new Carbon($event->Timestamp);
                    if ($event->EventType == 'DL') {
                        $statusEntrega = 10;
                        $fechaEntrega = $timestamps->format('Y-m-d H:i:s');
                    }
                    $evento = new stdClass();
                    $evento->fecha_entrega = $timestamps->format('Y-m-d H:i:s');
                    $evento->codigo_evento = $event->EventType;
                    $evento->evento = $event->EventDescription;
                    $evento->codigo_ubicacion = $direccion->StateOrProvinceCode ?? '';
                    $evento->ubicacion = $direccion->City ?? '';
                    $arrayEventos[] = $evento;
                }
                $rastreo->status_entrega = $statusEntrega;
                $rastreo->fecha_entrega = $fechaEntrega ?? null;
                $rastreo->fecha_envio = isset($timestamps) ? $timestamps->format('Y-m-d H:i:s') : null;
                $rastreo->eventos = $arrayEventos;
            }
            $arrayRastreos[] = $rastreo;
        }
        return $first ? $arrayRastreos[0] : $arrayRastreos;
    }

    public function getTipoServicio()
    {
        return self::$arrayServiciosPermitidos[0];
    }

    public function recoleccion(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $arrayRequest = $this->configuracionRecoleccionGuia($guiaMensajeriaTO);
//        die(print_r($arrayRequest));
        $createPickupReply = $arrayRequest['createPickupReply'];
        $notifications = $createPickupReply->Notifications;
        $recoleccion = new stdClass();
        $recoleccion->status = $notifications->Severity;
        $recoleccion->mensaje = $notifications->Message;
        $recoleccion->location = $this->location;
        if ($notifications->Severity != 'ERROR'
            && $notifications->Severity != 'FAILURE') {
            $recoleccion->pick_up = $createPickupReply->PickupConfirmationNumber;
            $recoleccion->localizacion = $createPickupReply->Location;
            $recoleccion->request = $arrayRequest['request'];
            $recoleccion->response = $arrayRequest['response'];
        }
        return $recoleccion;
    }

    private function configuracionRecoleccionGuia(GuiaMensajeriaTO $guiaMensajeriaTO)
    {
        $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();

        if ($bitacoraMensajeriaOrigenTO instanceof BitacoraMensajeriaOrigenTO
            && $bitacoraCotizacionMensajeriaTO instanceof BitacoraCotizacionMensajeriaTO) {
            $fecha = $bitacoraCotizacionMensajeriaTO->getFechaLiberacion();
            $mensajeriaTO = new MensajeriaTO();

            $mensajeriaTO->setCodigoPostalDestino($bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino());
            $mensajeriaTO->setCodigoPostalOrigen($bitacoraCotizacionMensajeriaTO->getCodigoPostalOrigen());
            $mensajeriaTO->buscarSiglasSepomex();

            $createPickupRequest = new \FedEx\PickupService\ComplexType\CreatePickupRequest();
            // Authentication & client details.
            $createPickupRequest->WebAuthenticationDetail->UserCredential->Key
                = $this->configuracion->get('key');
            $createPickupRequest->WebAuthenticationDetail->UserCredential->Password
                = $this->configuracion->get('password');
            $createPickupRequest->ClientDetail->AccountNumber = $this->configuracion->get('shipAccount');
            $createPickupRequest->ClientDetail->MeterNumber = $this->configuracion->get('meter');
            // Version.
            $createPickupRequest->Version->ServiceId = 'disp';
            $createPickupRequest->Version->Major = 22;
            $createPickupRequest->Version->Intermediate = 0;
            $createPickupRequest->Version->Minor = 0;
            $createPickupRequest->TransactionDetail->CustomerTransactionId = 'create pickup request example';
            $createPickupRequest->TransactionDetail->Localization->LanguageCode = 'EN';
            $createPickupRequest->TransactionDetail->Localization->LocaleCode = 'ES';
            // Associated account number.
            $createPickupRequest->AssociatedAccountNumber->Type = \FedEx\PickupService\SimpleType\AssociatedAccountNumberType::_FEDEX_EXPRESS;
            $createPickupRequest->AssociatedAccountNumber->AccountNumber = $this->configuracion->get('shipAccount');
            // Origin detail contact.
            $createPickupRequest->OriginDetail->PickupLocation->Contact->PersonName = $bitacoraMensajeriaOrigenTO->getNombre().' '.$bitacoraMensajeriaOrigenTO->getApellidos();
            $createPickupRequest->OriginDetail->PickupLocation->Contact->CompanyName = 'T1 Envios';
            $createPickupRequest->OriginDetail->PickupLocation->Contact->PhoneNumber = $bitacoraMensajeriaOrigenTO->getTelefono();
            $createPickupRequest->OriginDetail->PickupLocation->Contact->EMailAddress = $bitacoraMensajeriaOrigenTO->getEmail();
            // Origin detail address.
            $createPickupRequest->OriginDetail->PickupLocation->Address->StreetLines = [$bitacoraMensajeriaOrigenTO->getDireccionCompuesta()];
            $createPickupRequest->OriginDetail->PickupLocation->Address->City = $bitacoraMensajeriaOrigenTO->getEstado();
            $createPickupRequest->OriginDetail->PickupLocation->Address->StateOrProvinceCode = $mensajeriaTO->getSiglasCodigoOrigen();
            $createPickupRequest->OriginDetail->PickupLocation->Address->PostalCode = $bitacoraCotizacionMensajeriaTO->getCodigoPostalOrigen();
            $createPickupRequest->OriginDetail->PickupLocation->Address->CountryCode = 'MX';
            $createPickupRequest->OriginDetail->PackageLocation = \FedEx\PickupService\SimpleType\PickupBuildingLocationType::_FRONT;
            $createPickupRequest->OriginDetail->BuildingPart = \FedEx\PickupService\SimpleType\BuildingPartCode::_SUITE;
            $createPickupRequest->OriginDetail->BuildingPartDescription = 'Building part description';
            //$createPickupRequest->OriginDetail->ReadyTimestamp = $fecha->format('c');
            $diaPickup = diaRecoleccion();
            $createPickupRequest->OriginDetail->ReadyTimestamp = $diaPickup->format('Y-m-d\T12:i:sP');
            $createPickupRequest->OriginDetail->CompanyCloseTime = '19:00:00';
            //$createPickupRequest->OriginDetail->Location = 'NQAA';
            $createPickupRequest->OriginDetail->SuppliesRequested = 'supplies requested';
            $createPickupRequest->PackageCount = 1;
            $createPickupRequest->TotalWeight->Units = SimpleType\WeightUnits::_KG;
            $createPickupRequest->TotalWeight->Value =  $bitacoraCotizacionMensajeriaTO->getPeso();
            $createPickupRequest->CarrierCode = SimpleType\CarrierCodeType::_FDXE;
            $createPickupRequest->OversizePackageCount = 0;
            //$createPickupRequest->Remarks = 'remarks';
            $createPickupRequest->CommodityDescription = 'test environment - please do not process pickup';
            $createPickupRequest->CountryRelationship = \FedEx\PickupService\SimpleType\CountryRelationshipType::_DOMESTIC;
            $request = new \FedEx\PickupService\Request();
            $request->getSoapClient()->__setLocation($this->configuracion->get('location').'pickup');
            $createPickupReply = $request->getCreatePickupReply($createPickupRequest, true);
            Log::info($request->getSoapClient()->__getLastResponse());
            return [
                'createPickupReply'=>$createPickupReply,
                'response'=>$request->getSoapClient()->__getLastResponse(),
                'request'=>$request->getSoapClient()->__getLastRequest()
            ];
        }
    }

    public function recoleccionMensajeria(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        Log::info('Recoleccion mensajeria: '.$mensajeriaRecoleccionTO->getmensajeria()->clave);
        $arrayRequest = $this->configuracionRecoleccionMensajeria($mensajeriaRecoleccionTO);
        Log::info('Response recoleccion:  ');
        Log::info($arrayRequest['response']);

        $createPickupReply = $arrayRequest['createPickupReply'];
        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadXML( $arrayRequest['response'] );
        $responseFaultstring    = $doc->getElementsByTagName("faultstring");
        $recoleccion = new stdClass();
        $notifications = $createPickupReply ?$createPickupReply->Notifications:null;
        $recoleccion->status = $notifications??500;
        $recoleccion->location = $this->location;

        if($responseFaultstring->length > 0){
//            die(print_r($responseFaultstring->length));
            $responseDesc     = $doc->getElementsByTagName("desc");
            throw new \Exception( $responseDesc->item(0)->nodeValue);
        }

        if ($notifications->Severity != 'ERROR' && $notifications->Severity != 'FAILURE') {
            $recoleccion->mensaje = $notifications->LocalizedMessage;
            $recoleccion->pick_up = $createPickupReply->PickupConfirmationNumber;
            $recoleccion->localizacion = $createPickupReply->Location;
            $recoleccion->request = $arrayRequest['request'];
            $recoleccion->response = $arrayRequest['response'];
        }else{
            throw new \Exception($notifications->Message);
        }

        //$recoleccion->response = $response->asXML();
        return $recoleccion;
    }

    private function configuracionRecoleccionMensajeria(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
        $datos = $mensajeriaRecoleccionTO->getDatos();
        $sepomex = new Sepomex();
        $sepomex = $sepomex->obtenerSiglasEDO($datos['codigo_postal']);

        if(!$sepomex){
            throw new \Exception("No se encontró el código postal en sepomex");
        }

        if(env('API_LOCATION') == 'test' ){
            $this->configuracion = $this->getTestKeys();
        }

        $createPickupRequest = new \FedEx\PickupService\ComplexType\CreatePickupRequest();
        // Authentication & client details.
        $createPickupRequest->WebAuthenticationDetail->UserCredential->Key
            = $this->configuracion->get('key');
        $createPickupRequest->WebAuthenticationDetail->UserCredential->Password
            = $this->configuracion->get('password');
        $createPickupRequest->ClientDetail->AccountNumber = $this->configuracion->get('shipAccount');
        $createPickupRequest->ClientDetail->MeterNumber = $this->configuracion->get('meter');
        // Version.
        $createPickupRequest->Version->ServiceId = 'disp';
        $createPickupRequest->Version->Major = 22;
        $createPickupRequest->Version->Intermediate = 0;
        $createPickupRequest->Version->Minor = 0;
        $createPickupRequest->TransactionDetail->CustomerTransactionId = 'create pickup request example';
        $createPickupRequest->TransactionDetail->Localization->LanguageCode = 'EN';
        $createPickupRequest->TransactionDetail->Localization->LocaleCode = 'ES';
        // Associated account number.
        $createPickupRequest->AssociatedAccountNumber->Type = \FedEx\PickupService\SimpleType\AssociatedAccountNumberType::_FEDEX_EXPRESS;
        $createPickupRequest->AssociatedAccountNumber->AccountNumber = $this->configuracion->get('shipAccount');
        // Origin detail contact.
        $createPickupRequest->OriginDetail->PickupLocation->Contact->CompanyName = 'T1 Envios';
        $createPickupRequest->OriginDetail->PickupLocation->Contact->PhoneNumber = $datos['telefono'];
        $createPickupRequest->OriginDetail->PickupLocation->Contact->EMailAddress = $datos['email'];
        // Origin detail address.
        $createPickupRequest->OriginDetail->PickupLocation->Address->StreetLines = $datos['calle'].' '.$datos['numero'].' '.$datos['colonia'];
        $createPickupRequest->OriginDetail->PickupLocation->Address->City = $datos['estado'];
        $createPickupRequest->OriginDetail->PickupLocation->Address->StateOrProvinceCode = $sepomex->sigla;
        $createPickupRequest->OriginDetail->PickupLocation->Address->PostalCode = $datos['codigo_postal'];
        $createPickupRequest->OriginDetail->PickupLocation->Address->CountryCode = 'MX';
        $createPickupRequest->OriginDetail->PackageLocation = \FedEx\PickupService\SimpleType\PickupBuildingLocationType::_FRONT;
        $createPickupRequest->OriginDetail->BuildingPart = \FedEx\PickupService\SimpleType\BuildingPartCode::_SUITE;
        $createPickupRequest->OriginDetail->BuildingPartDescription = 'Building part description';
        //$createPickupRequest->OriginDetail->ReadyTimestamp = $fecha->format('c');
        $horaInicio=  new Carbon($datos['hora_inicio']);
        $hora = $horaInicio->addHour()->format('H:i:s');

        $diaPickup = new Carbon($datos['fecha'].' '.$hora);
        $fechaFin = new Carbon($datos['horario_cierre']);
        $createPickupRequest->OriginDetail->ReadyTimestamp = $diaPickup->format('c');
        $createPickupRequest->OriginDetail->CompanyCloseTime = $fechaFin->format("H:i:s");
        //$createPickupRequest->OriginDetail->Location = 'NQAA';
        $createPickupRequest->OriginDetail->SuppliesRequested = 'supplies requested';
        $createPickupRequest->PackageCount = $datos['cantidad_piezas'];
        $createPickupRequest->TotalWeight->Units = SimpleType\WeightUnits::_KG;
        $createPickupRequest->TotalWeight->Value =  $datos['peso'];
        $createPickupRequest->CarrierCode = SimpleType\CarrierCodeType::_FDXE;
        $createPickupRequest->OversizePackageCount = 0;
        //$createPickupRequest->Remarks = 'remarks';
        $createPickupRequest->CommodityDescription = 'test environment - please do not process pickup';
        $createPickupRequest->CountryRelationship = \FedEx\PickupService\SimpleType\CountryRelationshipType::_DOMESTIC;
        $request = new \FedEx\PickupService\Request();
        Log::info($createPickupRequest->toArray());

        $request->getSoapClient()->__setLocation($this->configuracion->get('location').'pickup');
        $createPickupReply = null;
        try{
            $createPickupReply = $request->getCreatePickupReply($createPickupRequest, true);

        }catch (\Exception $exception){
//            $createPickupReply = $request->getSoapClient()->__getLastResponse();
            Log::info($request->getSoapClient()->__getLastResponse());

        }
        $response = $request->getSoapClient()->__getLastResponse();
        return [
            'createPickupReply'=>$createPickupReply,
            'response'=>$response,
            'request'=>$request->getSoapClient()->__getLastRequest()
        ];

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

    public function validarCampos(){
        $rules = [
            "key" => 'required',
            "password" => 'required',
            "shipAccount" => 'required',
            "meter" => 'required',
        ];

        return $rules;
    }

    public function getCodeResponse()
    {
        return $this->code_response;
    }

    public function setCodeResponse($codeResponse): void
    {
        $this->code_response = $codeResponse;
    }

    private function getTestKeys(){

        return collect([
            'key'          => 'LgiWacWCmeeZlgQq',
            'password'     => 'v1KJYPOKhqVcsSOXVJsSbNrDe',
            'shipAccount'  => '510087763',
            'billAccount'  => '510087763',
            'trackAccount' => '510087763',
            'meter'        => '118637391',
            'location' => 'https://wsbeta.fedex.com:443/web-services/'
        ]);

//        return collect([
//            "key" => 'uhUKZEKBws4bxQ0G',//'LgiWacWCmeeZlgQq',
//            "password" => '4nP2gUyrs4fYmP91or7VGTdq7',//'v1KJYPOKhqVcsSOXVJsSbNrDe',
//            "shipAccount" => '569411742',//'510087763',
//            "meter" => '106998909',//'118637391',
//            'location' => self::TESTING_URL
//        ]);


    }

    public function cartaPorte(GuiaMensajeriaTO $guiaMensajeriaTO,Comercio $comercio){
        try {
            Log::info("Se envia carta porte para guia: ".$guiaMensajeriaTO->getGuia());
            Log::info($this->url_carta_porte);
            $invoice_date = date('Y-m-d\TH:i:sO');
            $cotizacion = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
            $pesoUnitarioProd = (($cotizacion->getAlto() * $cotizacion->getAncho() * $cotizacion->getLargo()) / 5000);
            Log::info(" PesoI:".$pesoUnitarioProd);
            if($pesoUnitarioProd < 0.5)
            {
                $pesoUnitarioProd = 0.5;
            }
            Log::info(" PesoF:".$pesoUnitarioProd);

            $producto = array(
                "bienesTransp"         => $guiaMensajeriaTO->getClaveProductoSAT(),
                "descripcion"          => $guiaMensajeriaTO->getContenido(),
                "cantidad"             => 1,
                "claveUnidad"          => "H87",
                "unidad"               => "piezas",
                "dimensiones"          => $cotizacion->getAlto() . "/" . $cotizacion->getAncho() . "/" . $cotizacion->getLargo() . "cm",#"15/30/10cm",""
                "materialPeligroso"    => "",
                "cveMaterialPeligroso" => "",
                "embalaje"             => "Tu",
                "descripEmbalaje"      => "cajas de carton",
                "pesoEnKg"             => $pesoUnitarioProd,#"0.200",
                "valorMercancia"       => $cotizacion->getValorPaquete(),
                "moneda"               => "MXN",
                "fraccionArancelaria"  => "",
                "uuidComercioExt"      => ""
            );

            $guiaMensajeria = GuiaMensajeria::with(['bitacoraMensajeriaOrigen','bitacoraMensajeriaDestino'])
                ->where('guia',$guiaMensajeriaTO->getGuia())->first();
            $origen = $guiaMensajeria->bitacoraMensajeriaOrigen;
            $destino = $guiaMensajeria->bitacoraMensajeriaDestino;

            $ubicaciones = array(
                    "ubicacion" => array(
                        array(
                            "origen" => array(
                                "idOrigen"         => "OR" . $origen->id,
                                "rfcRemitente"     => $comercio->rfc,
                                "nombreRemitente"  => $comercio->descripcion,
                                "numRegIdTrib"     => "",
                                "residenciaFiscal" => "MEX",
                                "fechaHoraSalida"  => $invoice_date
                            ),
                            "domicilio" => array(
                                "calle"          => $origen->calle,
                                "numeroExterior" => $origen->numero,
                                "numeroInterior" => "",
                                "colonia"        => $origen->colonia,
                                "localidad"      => "",
                                "referencia"     => $origen->referencias,
                                "municipio"      => $origen->municipio,
                                "estado"         => $origen->estado,
                                "pais"           => "MEX",
                                "codigoPostal"   => $cotizacion->getCodigoPostalOrigen()
                            )
                        ),
                        array(
                            "destino" => array(
                                "idOrigen"           => "DE" . $destino->id,
                                "rfcDestinatario"    => "XAXX010101000",
                                "nombreDestinatario" => $destino->nombre.' '.$destino->apellidos,
                                "numRegIdTrib"       => "",
                                "residenciaFiscal"   => "MEX",
                                "fechaHoraSalida"    => $invoice_date
                            ),
                            "domicilio" => array(
                                "calle"          => $destino->calle,
                                "numeroExterior" => $destino->numero,
                                "numeroInterior" => '',
                                "colonia"        => $destino->colonia,
                                "localidad"      => "",
                                "referencia"     => $destino->referencias,
                                "municipio"      => $destino->municipio,
                                "estado"         => $destino->estado,
                                "pais"           => "MEX",
                                "codigoPostal"   => $cotizacion->getCodigoPostalDestino()
                            )
                        )
                    )
                );

            $mercancias   = array(
                "numTotalMercancias" => 1,
                "mercancia" => [$producto]
            );

            $comment = "JSON Standar File to receive FedEx customer information and create the Carta Porte requested by SAT (Mexican Authority TAX) Values just as an exmaple, please remove these and use your own values ";

            $result = array(
                "comment" => $comment,
                "customerCartaPorteFDX" => array(
                    "guia" => $guiaMensajeriaTO->getGuia(),
                    "ubicaciones" => $ubicaciones,
                    "mercancias"  => $mercancias
                )
            );

            $data_string = json_encode($result);
            Log::info(' CartaPorte-request: '.$data_string);

            $options = [
                "json" => $result,
                'connect_timeout' => 90,
                'http_errors' => true,
                'verify' => false,
                'headers' => [
                    'Content-Type' => 'application/json'
                ]
            ];

            $client = new Client();
            $request = $client->request("POST", $this->url_carta_porte, $options);
            $response = $request->getBody()->getContents();
            Log::info(' CartaPorte-response: '.json_encode($response));
            $success = true;

        }catch (\Exception $e){
            Log::error("Error carta porte: ". $e->getMessage());
            $success = false;
            $response = $e->getMessage();
//            die(print_r($log));
        }
        Log::info(' Guarda log carta porte guia: '.$guiaMensajeriaTO->getGuia());
        $log = new BitacoraCartaPorteResponse();
        $log->id_guia_mensajeria = $guiaMensajeriaTO->getId();
        $log->request       = $data_string;
        $log->response     = $response;
        $log->save();

        return [
            "success" => $success,
            "message" => $response
        ];
    }
}
