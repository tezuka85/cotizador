<?php

namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;
use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraCartaPorteResponse;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaDestinoTO;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigen;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\FedexRestFul\PickupService;
use App\ClaroEnvios\Mensajerias\FedexRestFul\RateService;
use App\ClaroEnvios\Mensajerias\FedexRestFul\ShipService;
use App\ClaroEnvios\Mensajerias\GuiaExcedente\GuiaExcedente;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use App\ClaroEnvios\Mensajerias\Track\TrackMensajeriaResponse;
use App\ClaroEnvios\Respuestas\Response;
use App\ClaroEnvios\Sepomex\Sepomex;
use App\ClaroEnvios\Xml\Dhl;
use App\ClaroEnvios\ZPL\ZPL;
use App\Exceptions\ValidacionException;
use Carbon\Carbon;
use FedEx\CourierDispatchService\ComplexType\Weight;
use FedEx\OpenShipService\ComplexType\AddPackagesToOpenShipmentRequest;
use FedEx\OpenShipService\ComplexType\ConfirmOpenShipmentRequest;
use FedEx\OpenShipService\ComplexType\CreateOpenShipmentRequest;
use FedEx\OpenShipService\ComplexType\RetrieveOpenShipmentRequest;
use FedEx\OpenShipService\Request;
use FedEx\RateService\ComplexType;
use FedEx\RateService\SimpleType;
use FedEx\RateService\Request as RequestFedex;
use FedEx\TrackService;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use stdClass;
//use FedEx\ShipService;
use FedEx\ShipService\ComplexType\Money as ComplexTypeShipMoney;
use FedEx\ShipService\ComplexType\TrackingId;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\VarDumper\VarDumper;

/**
 * Class MensajeriaFedex
 * @package App\ClaroEnvios\Mensajerias
 * @version 2.0
 */
class MensajeriaFedex extends MensajeriaMaster implements MensajeriaCotizable
{
    public static $arrayServicioDescripcion = [
        'FEDEX_EXPRESS_SAVER' => 'Economico',
        'STANDARD_OVERNIGHT' => 'Dia Siguiente'
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
    protected $id_configuracion;
    protected $paquetes;
    protected $paquetes_detalle;

    use AccesoConfiguracionMensajeria;

    const PRODUCTION_URL = 'https://ws.fedex.com:443/web-services/';
    const TESTING_URL = 'https://wsbeta.fedex.com:443/web-services/';
    //    const TESTING_URL = 'https://ws.fedex.com:443/web-services/';

    const PRODUCTION_URL_CARTA_PORTE = "https://ws.fedex.com/LAC/ServicesAPI/mx/cartaporte/customers";

    const TESTING_URL_CARTA_PORTE = "https://wsbeta.fedex.com/LAC/ServicesAPI/mx/cartaporte/customers";

    //const TESTING_URL_CARTA_PORTE ="https://ws.fedex.com/LAC/ServicesAPI/mx/cartaporte/customers";


    /**
     * MensajeriaFedex constructor.
     */
    public function __construct($mensajeriaTO = false)
    {
        $location = env('API_LOCATION', 'test');
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
            $this->id_configuracion = $mensajeriaTO->getIdConfiguracion();
            $this->paquetes = $mensajeriaTO->getPaquetes();
            $this->paquetes_detalle = $mensajeriaTO->getPaquetesDetalle();
            $accesoComercioMensajeriaTO = new AccesoComercioMensajeriaTO();
            $accesoComercioMensajeriaTO->setComercioId($mensajeriaTO->getComercio());
            $accesoComercioMensajeriaTO->setMensajeriaId($mensajeriaTO->getId());

            //SI es negiciación 1-t1envios se toman las llaves de mensajeria de t1envios
            if ($mensajeriaTO->getNegociacionId() == 1) {
                $accesoComercioMensajeriaTO->setComercioId(1);
            }

            $this->configurarAccesos($accesoComercioMensajeriaTO);

            if (isset($this->configuracion)) {
                if ($location == 'produccion' || $location == 'release') {
                    $this->configuracion->put('location', self::PRODUCTION_URL);
                    $this->location = env('API_LOCATION');
                    $this->url_carta_porte = self::PRODUCTION_URL_CARTA_PORTE;
                } else {
                    $this->configuracion->put('location', self::TESTING_URL);
                    $this->location = env('API_LOCATION');
                    $this->url_carta_porte = self::TESTING_URL_CARTA_PORTE;
                }
            } else {
                throw new \Exception("No cuenta con llaves de accceso a la mensajeria Fedex");
            }
            Log::info('Comercio: ' . $mensajeriaTO->getComercio() . ', ' . 'Negociacion: ' . $mensajeriaTO->getNegociacionId());
            Log::info('Llaves comercio: ' . $accesoComercioMensajeriaTO->getComercioId());
            Log::info('Ambiente .env: ' . $location);



            if (!$mensajeriaTO->getTabulador()) {
                Log::info($this->configuracion);
            }
            //            die(print_r($this->configuracion));
        }
    }

    public function rate($traerResponse = false, $peso = null)
    {
        Log::info("ENTRANDO AL RATE DE FEDEX");
        $arrayServiciosPermitidos = [
            'FEDEX_EXPRESS_SAVER',
            'STANDARD_OVERNIGHT'
        ];
        $response = $this->ratePeticion();
        Log::info("RESPONSE RATE FEDEX:");
        Log::info($response);
        // die(print_r($response));
        $tarificador = new stdClass();
        $tarificador->success = true;
        $tarificador->message = Response::$messages['successfulSearch'];
        if ($traerResponse) {
            $tarificador->request = json_encode($response['request']);
            $tarificador->response = json_encode($response['response']);
        }
        if (!array_key_exists('error', $response)) {

            $rateReply = $response['response']['output']['rateReplyDetails'];
            $this->code_response = 200;
            Log::info('RateReplyDetails');

            if ($rateReply) {
                foreach ($rateReply as $data) {
                
                    $data = json_decode(json_encode($data));

                    if (in_array($data->serviceType, $arrayServiciosPermitidos)) {
                        Log::info('Entra en servicios permitidos: ' . $data->serviceType);
                        if (!empty($data->ratedShipmentDetails)) {
                            foreach ($data->ratedShipmentDetails as $ratedShipmentDetail) {
                                Log::info('Entra en servicios RateType: ' . $data->serviceType);
                                $tipo_costo = $ratedShipmentDetail->ratedPackages['0']->packageRateDetail->rateType;
                                Log::info($tipo_costo);

                                switch ($tipo_costo) {
                                    case 'PAYOR_LIST_SHIPMENT':
                                        $costo = $ratedShipmentDetail->totalNetCharge;

                                        //19.92
                                        break;
                                    case 'PAYOR_ACCOUNT_SHIPMENT':
                                        $costo_claro = $ratedShipmentDetail->totalNetCharge;
                                        //19.43
                                        break;
                                        //PREFERRED_ACCOUNT_SHIPMENT: 369.62
                                        //PREFERRED_LIST_SHIPMENT:378.94
                                    case 'PAYOR_LIST_PACKAGE':
                                        $costo_claro = $ratedShipmentDetail->totalNetCharge;
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
                            Log::info(' Costo margen: ' . $this->costo);
                        } elseif ($this->porcentaje != 0) {
                            $costoAdicional = round($costo_claro * ($this->porcentaje / 100), 2);
                            Log::info(' Porcentaje margen: ' . $this->porcentaje);
                        }

                        Log::info(' Costo guia mensajeria FEDEX: ' . $costo_claro);
                        if (in_array($this->id_configuracion, array_merge(ConfiguracionComercio::$comerciosZonas,[2,9]))) {
                            Log::info(' Calculo zonas: ');
                            $costoGuia = $costo_claro;
                            $costoTotalCalculado = round(($costoGuia / (1 - ($this->porcentaje / 100))), 2);
                            Log::info('Costo Total zonas: ' . $costoTotalCalculado);
                        } else {
                            Log::info(' Calculo default: ');
                            $costoSeguro = $this->seguro ? round($this->valor_paquete * ($this->porcentaje_seguro / 100), 2) : 0;
                            Log::info(' Costo adicional calculado: ' . $costoAdicional);
                            Log::info(' Costo Seguro ' . $costoSeguro);
                            $costoTotalCalculado = round($costo_claro + $costoAdicional + $costoSeguro, 2);
                            Log::info(' Costo Total: ' . $costoTotalCalculado);
                        }

                        $totalPaquetes = 0;
                        if ($this->paquetes){
                            $totalPaquetes = $this->paquetes;
                        }
                        $servicioMensajeria = $this->obtenerServicioMensajeria('Fedex', $data->serviceType);
                        $servicio = $this->responseService($costo_claro, $costo_claro, $costoTotalCalculado, $servicioMensajeria, null, $costoSeguro,false ,$totalPaquetes);
                        $tarificador->servicios->{$data->serviceType} = $servicio;
                        $tarificador->location = $this->location;
                        $tarificador->code_response = $this->code_response;

                    }

                }
                
                if (property_exists($tarificador, 'servicios')) {
                    return $tarificador;
                }
                else{
                    $this->code_response = 400;
                    Log::info('Al terminar NO existe tarificador Servicios');
                    $tarificador->success = false;
                    $tarificador->servicios = new stdClass();
                    $tarificador->codigo = 400;
                    $tarificador->message = "No hay servicios disponibles para este requerimiento";
                }
            }

        } else {

            Log::info('Erorr tarificador Servicios Fedex');
            $tarificador->success = false;
            $tarificador->code_response = $response['code'];
            $tarificador->servicios = new stdClass();
            $tarificador->message = $response['error'];
        }
        //   }

        //die(print_r($tarificador));
        Log::info('termina cotizacion');
        return $tarificador;
    }

    public function rateInternational($traerResponse = false, $seguro = false)
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

        Log::info("RESPONSE :" . $rateReply->Notifications[0]->Message);
        Log::info('Estatus: ' . $rateReply->HighestSeverity);
        $this->code_response = $rateReply->HighestSeverity == 'SUCCESS' || $rateReply->HighestSeverity == 'NOTE' ? 200 : 400;
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
                $this->porcentaje = $margen;
                $costoAdicional = round(($costo_claro * $margen) / 100, 2);
                Log::info(' Costo mensajeria ' . $costo_claro);
                Log::info(' Margen: ' . $margen);
                Log::info(' Costo adicional internacional ' . $costoAdicional);
                //                die(print_r($this->porcentaje ));
                //$costo_claro = $costo_claro + $costoAdicional;
                Log::info(' Costo internacional ' . $costo_claro);

                $valorPaquete = $this->valor_paquete;
                $costoSeguro = 0;
                Log::info(' Costo Serguro ' . $costoSeguro);
                $costoTotalCalaculado = round($costo_claro + $costoAdicional + $costoSeguro, 2);
                Log::info('--Costo total ' . $costoTotalCalaculado);

                $servicioMensajeria =  $this->obtenerServicioMensajeria('Fedex', $data->ServiceType);
                $servicio = $this->responseService($costo_claro, $costo_claro, $costoTotalCalaculado, $servicioMensajeria, null, $costoSeguro, true);
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
        $cotizacion = $this->setDatosCotizacion();
        
        $fedexRate = new RateService();
        $fedexRate->setData($cotizacion);
        $responseRate = $fedexRate->makeRequest('rate/v1/rates/quotes');

        if (is_array($responseRate)) {
            if (array_key_exists('error', $responseRate)) {
                Log::info('Problema al generar Cotizacion FEDEX: ');
                Log::info($responseRate);
                $notification = $responseRate['error'];

                if (is_object($notification)) {
                    $error = $notification->errors[0];
                } else {
                    $error = $notification[0];
                }

                //throw new ValidacionException('Fedex Error Cotizacion: '.$error->code . ' ' . $error->message);
                return [
                    'request' => $fedexRate->getData(),
                    'response' => $error,
                    'error' => $error->message,
                    'code' => 400

                ];
            }
        }
        $response = $responseRate->getBody()->getContents();

        $this->request = json_encode($fedexRate->getData());
        $this->response = is_string($response) ??  json_encode($response);
        $this->code_response = $responseRate->getStatusCode();

        return [
            'request' => $fedexRate->getData(),
            'response' => is_string($response) ? json_decode($response, true) : json_encode($response)
        ];
    }

    public function setDatosCotizacion()
    {
        //die(print_r($this));

        $data = [
            'moneda' => $this->moneda,
            'siglas_codigo_postal_origen' => $this->siglas_codigo_postal_origen,
            'siglas_codigo_postal_destino' => $this->siglas_codigo_postal_destino,
            'codigo_postal_origen' => $this->codigo_postal_origen,
            'codigo_postal_destino' => $this->codigo_postal_destino,
            'peso_calculado' => $this->peso_calculado,
            'valor_paquete' => $this->valor_paquete,
            'largo' => $this->largo,
            'ancho' => $this->ancho,
            'alto' => $this->alto,
            'seguro' => $this->seguro,
            'paquetes' => ($this->paquetes!=null) ? $this->paquetes : 0,
            'paquetes_detail'=>($this->paquetes_detalle!=null) ? $this->paquetes_detalle : null
        ];
        return $data;
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
        $rateRequest->TransactionDetail->CustomerTransactionId = 'T1envios rate internacional'; //puede ir cualquier id interno
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
        $money->setCurrency($this->moneda); //NO PERMITE MXN

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
        $rateServiceRequest->getSoapClient()->__setLocation($url . 'rate'); //use production URL
        $rateReply = $rateServiceRequest->getGetRatesReply($rateRequest, false); // send true as the 2nd argument to return the SoapClient's stdClass response.
        $request  = $rateServiceRequest->getSoapClient()->__getLastRequest();
        $response = $rateServiceRequest->getSoapClient()->__getLastResponse();
        //        die(print_r($request));

        return [
            'request' => $request,
            'response' => $response,
            'rateReply' => $rateReply
        ];
    }

    public function generarGuia(GuiaMensajeriaTO $guiaMensajeriaTO)
    {

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

    public function verificarExcedente($response)
    {
        //        die(print_r($response));

        $xmlResponse = new \DOMDocument();
        $xmlResponse->preserveWhiteSpace = FALSE;
        $xmlResponse->loadXML($this->getResponse());
        $codes = $xmlResponse->getElementsByTagName('Code');
        $entregado = false;
        foreach ($codes as $code) {
            if ($code->nodeValue == 'DL') {
                $entregado = true;
                break;
            }
        }

        if (!$entregado) {
            $pesoPaquete = $response->CompletedTrackDetails->TrackDetails[0]->PackageWeight;
            //buscar si existe un excedente
            $excedente = GuiaMensajeria::select('guias_mensajerias.*', DB::raw('guias_excedentes.id as guia_excedente_id'))
                ->leftjoin('guias_excedentes', 'guias_mensajerias.guia', '=', 'guias_excedentes.guia')
                ->where('guias_mensajerias.guia', $this->getGuiaMensajeria()->guia)
                ->where('guias_mensajerias.status_entrega', '!=', GuiaMensajeria::$status['entregada'])
                ->get()->last();
            $excedentePeso = 0;
            if (!$excedente->guia_excedente_id) {
                $bitacoraCotizacion = BitacoraCotizacionMensajeria::findOrFail($excedente->bitacora_cotizacion_mensajeria_id);
                //  die("<pre>".print_r( $excedente->toArray()));

                $pesoTrack = $pesoPaquete->Value;
                if ($pesoPaquete->Units != 'KG') {
                    $pesoTrack = conversionKilogramos($pesoPaquete->Units, $pesoPaquete->Value);
                }

                $pesoTrack = 20;
                if ($pesoTrack > $bitacoraCotizacion->peso) {
                    $excedentePeso = $pesoTrack - $bitacoraCotizacion->peso;
                }

                if ($excedentePeso > 0) {
                    $guiaExcendente = new GuiaExcedente();
                    $guiaExcendente->guia_mensajeria_id = $excedente->id;
                    $guiaExcendente->guia = $excedente->guia;
                    $guiaExcendente->excedente_peso = $excedentePeso;
                    $guiaExcendente->save();
                }
            }
        }
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

    public function validarCampos()
    {
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

    private function getTestKeys()
    {

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
}
