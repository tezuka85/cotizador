<?php

namespace App\ClaroEnvios\Mensajerias\FedexRestFul;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\GuiaMensajeriaTO;
use App\ClaroEnvios\ZPL\ZPL;
use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;
use stdClass;

class ShipService extends FedexRestFul
{


    public function __construct()
    {
    }

    public function setData(GuiaMensajeriaTO $guiaMensajeriaTO, $siglasEstadoOrigen, $siglasEstadoDestino, $formato="zpl")
    {
        $bitacoraMensajeriaDestinoTO = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
        $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
        $comercio = Comercio::where('id', $guiaMensajeriaTO->getComercioId())->first();
        $fecha = $bitacoraCotizacionMensajeriaTO->getFechaLiberacion();
        $paquetes = ($bitacoraCotizacionMensajeriaTO->getPaquetes()!=null) ? $bitacoraCotizacionMensajeriaTO->getPaquetes(): 1;
        Log::info('Paquetes Fedex: '.$paquetes);
        $arrayPaquetes=[];

        $paquetesDetail = ($bitacoraCotizacionMensajeriaTO->getPaquetesDetalle()!=null) ? $bitacoraCotizacionMensajeriaTO->getPaquetesDetalle(): null;
        
        if($paquetesDetail != null ){
            Log::info('Tiene paquete detalle');

            for($i = 0; $i < count($paquetesDetail) ; $i++){
                $sequenceNumber = $i+1;
                $pkg = [
                        'sequenceNumber' => ''.$sequenceNumber.'',
                        'itemDescription' => $this->validarLongitudCadena($guiaMensajeriaTO->getContenido()),
                        'dimensions' => [
                            'length' => $paquetesDetail[$i]["largo"],
                            'width' => $paquetesDetail[$i]["ancho"],
                            'height' => $paquetesDetail[$i]["alto"],
                            'units' => 'CM'
                        ],
                        'weight' => [
                            'units' => 'KG',
                            'value' => $paquetesDetail[$i]["peso"]
                        ]
                    ];
                array_push($arrayPaquetes,$pkg);
            }

        }else if($paquetes > 1){
            Log::info('Tiene mas de una paquete');
            for($i = 0; $i < $paquetes; $i++){
                $sequenceNumber = $i+1;
                $pkg = [
                        'sequenceNumber' => ''.$sequenceNumber.'',
                        'itemDescription' => $this->validarLongitudCadena($guiaMensajeriaTO->getContenido()),
                        'dimensions' => [
                            'length' => $bitacoraCotizacionMensajeriaTO->getLargo(),
                            'width' => $bitacoraCotizacionMensajeriaTO->getAncho(),
                            'height' => $bitacoraCotizacionMensajeriaTO->getAlto(),
                            'units' => 'CM'
                        ],
                        'weight' => [
                            'units' => 'KG',
                            'value' => $bitacoraCotizacionMensajeriaTO->getPeso()
                        ]
                    ];
                array_push($arrayPaquetes,$pkg);
            }
           
        }else{
            $pkg = [
                    'sequenceNumber' => '1',
                    'itemDescription' => $this->validarLongitudCadena($guiaMensajeriaTO->getContenido()),
                    'dimensions' => [
                        'length' => $bitacoraCotizacionMensajeriaTO->getLargo(),
                        'width' => $bitacoraCotizacionMensajeriaTO->getAncho(),
                        'height' => $bitacoraCotizacionMensajeriaTO->getAlto(),
                        'units' => 'CM'
                    ],
                    'weight' => [
                        'units' => 'KG',
                        'value' => $bitacoraCotizacionMensajeriaTO->getPeso()
                    ]
                ];
            array_push($arrayPaquetes,$pkg);
        }

        //se setea formato de etiqueta y stock type
        
        $format = 'ZPLII';
        $stockType = 'STOCK_4X675_LEADING_DOC_TAB';
        if($formato=="pdf"){
            $format = 'PDF';
            $stockType = 'PAPER_4X675';
        }

        $data = [
            "mergeLabelDocOption" => "LABELS_AND_DOCS",
            'requestedShipment' => [
                'shipDatestamp' => now()->format('Y-m-d'),
                'shipper' => $this->dataShipper($bitacoraMensajeriaOrigenTO, $bitacoraCotizacionMensajeriaTO, $comercio, $siglasEstadoOrigen, $siglasEstadoDestino),
                'recipients' => [
                    [
                        'address' => [
                            'streetLines' => [
                                $this->validarLongitudCadena($bitacoraMensajeriaDestinoTO->getCalle(), 35),
                                $this->validarLongitudCadena("#Ext: " . $bitacoraMensajeriaDestinoTO->getNumero() ." Col: ".$bitacoraMensajeriaDestinoTO->getColonia(), 35)
                               ],
                            'city' => $this->validarLongitudCadena($bitacoraMensajeriaDestinoTO->getMunicipio(), 35),
                            'stateOrProvinceCode' => $siglasEstadoDestino,
                            'postalCode' => $bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino(),
                            'countryCode' => 'MX'
                        ],
                        'contact' => [
                            'personName' => $bitacoraMensajeriaDestinoTO->getNombre() . ' ' . $bitacoraMensajeriaDestinoTO->getApellidos(),
                            'emailAddress' => $bitacoraMensajeriaDestinoTO->getEmail(), //Revisar si dejo esto
                            'phoneNumber' => $bitacoraMensajeriaDestinoTO->getTelefono()
                        ]
                    ]
                ],
                'labelSpecification' => [
                    'labelFormatType' => 'COMMON2D',
                    'labelStockType' => $stockType, //para internacional es STOCK_4X6
                    'imageType' => $format

                ],
                'requestedPackageLineItems' => $arrayPaquetes,
                "pickupType" => "CONTACT_FEDEX_TO_SCHEDULE",
                "serviceType" => $bitacoraCotizacionMensajeriaTO->getTipoServicio(),
                "packagingType" => "YOUR_PACKAGING",
                "rateRequestType" => ["PREFERRED"],
                "totalPackageCount" => 1,

            ],
            "labelResponseOptions" => "LABEL",
            "accountNumber" => [
                "value" => env('FEDEX_ACCOUNT')
            ]
        ];

        $data['requestedShipment']['shippingChargesPayment']['paymentType'] = 'SENDER';
        $data['requestedShipment']['shippingChargesPayment']['payor']['responsibleParty'] =  $this->dataShipper($bitacoraMensajeriaOrigenTO, $bitacoraCotizacionMensajeriaTO, $comercio, $siglasEstadoOrigen, $siglasEstadoDestino);
        $data['requestedShipment']['shippingChargesPayment']['payor']['responsibleParty']['accountNumber']['value'] =  env('FEDEX_ACCOUNT');

        //preguntar si este es el seguro
        if ($bitacoraCotizacionMensajeriaTO->getSeguro()) {
            Log::info("Requiere seguro SI");
            $data['requestedShipment']['requestedPackageLineItems'][0]['declaredValue'] = [
                'amount' => number_format($bitacoraCotizacionMensajeriaTO->getValorPaquete(), 2, '.', ''),
                'currency' => 'NMP'
            ];
        }
        
        $this->data = $data;
        
        $this->endpoint = $this->baseUrl . 'ship/v1/shipments';
   
        Log::info(json_encode($data));
    }

    private function dataShipper(
        BitacoraMensajeriaOrigenTO $bitacoraMensajeriaOrigenTO,
        BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO,
        Comercio $comercio,
        $siglasEstado
    ) {
        $zpl = new ZPL();
        $data = [
            'address' => [
                'streetLines' => [
                    $this->validarLongitudCadena($zpl->removerAcentos($bitacoraMensajeriaOrigenTO->getCalle()),35),
                    $this->validarLongitudCadena("#Ext: ".$bitacoraMensajeriaOrigenTO->getNumero()." Col: ".$bitacoraMensajeriaOrigenTO->getColonia(),35)
                ],
                'city' => $this->validarLongitudCadena($bitacoraMensajeriaOrigenTO->getMunicipio(), 35),
                'stateOrProvinceCode' => $siglasEstado,
                'postalCode' => $bitacoraCotizacionMensajeriaTO->getCodigoPostalOrigen(),
                'countryCode' => 'MX'
            ],
            'contact' => [
                'personName' => $bitacoraMensajeriaOrigenTO->getNombre() . ' ' . $bitacoraMensajeriaOrigenTO->getApellidos(),
                'emailAddress' => $bitacoraMensajeriaOrigenTO->getEmail(),
                'phoneNumber' => $bitacoraMensajeriaOrigenTO->getTelefono(),
                'companyName' => $comercio->descripcion
            ]
        ];
       
        return $data;
    }

    public function getData()
    {
        return $this->data;
    }

    // public function setInternationalData(GuiaMensajeriaTO $guiaMensajeriaTO, $siglasEstadoOrigen, $siglasEstadoDestino)
    // {
    //     $bitacoraMensajeriaDestinoTO = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
    //     $bitacoraMensajeriaOrigenTO = $guiaMensajeriaTO->getBitacoraMensajeriaOrigenTO();
    //     $bitacoraCotizacionMensajeriaTO = $guiaMensajeriaTO->getBitacoraCotizacionMensajeriaTO();
    //     $comercio = Comercio::where('id', $guiaMensajeriaTO->getComercioId())->first();
    //     $fecha = $bitacoraCotizacionMensajeriaTO->getFechaLiberacion();

    //     $shipper = $this->dataShipper($bitacoraMensajeriaOrigenTO, $bitacoraCotizacionMensajeriaTO, $comercio, $siglasEstadoOrigen, $siglasEstadoDestino);
    //     $data = [
    //         "mergeLabelDocOption" => "LABELS_AND_DOCS",
    //         'requestedShipment' => [
    //             'shipDatestamp' => now()->format('Y-m-d'),
    //             'shipper' => $shipper,
    //             'recipients' => [
    //                 [
    //                     'address' => [
    //                         'streetLines' => [$bitacoraMensajeriaDestinoTO->getDireccionCompuesta() . ' ' . $bitacoraMensajeriaDestinoTO->getColonia()],
    //                         'city' => $bitacoraMensajeriaDestinoTO->getMunicipio(),
    //                         'stateOrProvinceCode' => $siglasEstadoDestino,
    //                         'postalCode' => $bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino(),
    //                         'countryCode' => 'pais destino',
    //                         "residential" => false
    //                     ],
    //                     'contact' => [
    //                         'personName' => $bitacoraMensajeriaDestinoTO->getNombre() . ' ' . $bitacoraMensajeriaDestinoTO->getApellidos(),
    //                         'emailAddress' => $bitacoraMensajeriaDestinoTO->getEmail(), //Revisar si dejo esto
    //                         'phoneNumber' => $bitacoraMensajeriaDestinoTO->getTelefono()
    //                     ]
    //                 ]
    //             ],
    //             'labelSpecification' => [
    //                 'labelFormatType' => 'COMMON2D',
    //                 'labelStockType' => 'STOCK_4X6',
    //                 'imageType' => 'ZPLII',
    //                 'labelPrintingOrientation' => 'BOTTOM_EDGE_OF_TEXT_FIRST',
    //                 'customerSpecifiedDetai' => [
    //                     'maskedData' => ['TRANSPORTATION_CHARGES_PAYOR_ACCOUNT_NUMBER', 'DUTIES_AND_TAXES_PAYOR_ACCOUNT_NUMBER']
    //                 ]
    //             ],
    //             'shippingDocumentSpecification' => [
    //                 'commercialInvoiceDetail' => [
    //                     'documentFormat' => [
    //                         'imageType' => 'PDF',
    //                         'stockType' => 'PAPER_LETTER',
    //                         'provideInstructions' => true,
    //                     ]
    //                 ]
    //             ],
    //             'shippingChargesPayment' => [
    //                 'paymentType' => 'SENDER',
    //                 'payor' => [
    //                     'responsibleParty' => $shipper,
    //                     'accountNumber'=> [
    //                         "value" =>  env('FEDEX_ACCOUNT')
    //                     ]
    //                 ]
    //             ],
    //             'requestedPackageLineItems' => [
    //                 [
    //                     'sequenceNumber' => '1',
    //                     'itemDescription' => $guiaMensajeriaTO->getContenido(),
    //                     'dimensions' => [
    //                         'length' => $bitacoraCotizacionMensajeriaTO->getLargo(),
    //                         'width' => $bitacoraCotizacionMensajeriaTO->getAncho(),
    //                         'height' => $bitacoraCotizacionMensajeriaTO->getAlto(),
    //                         'units' => 'CM'
    //                     ],
    //                     'weight' => [
    //                         'units' => 'KG',
    //                         'value' => $bitacoraCotizacionMensajeriaTO->getPeso()
    //                     ]
    //                 ]
    //             ],
    //             'customsClearanceDetail'=>[ 
    //                 'dutiesPayment' => [
    //                     'payor' => [
    //                         'responsibleParty'=> [
    //                             'accountNumber'=> [
    //                                 "value" =>  env('FEDEX_ACCOUNT')
    //                             ]
    //                         ]
    //                     ]//preguntar setDocumentContent('NON_DOCUMENTS');
    //                 ],
    //                 'totalCustomsValue' => [
    //                     'amount' => $bitacoraCotizacionMensajeriaTO->getValorPaquete(),
    //                      'currency' => $bitacoraCotizacionMensajeriaTO->getMoneda()
    //                 ],
    //                 'commodities' => [
    //                     'name' => $guiaMensajeriaTO->getContenido(),
    //                     'numberOfPieces' =>1,
    //                     'description' =>$guiaMensajeriaTO->getContenido(),
    //                     'countryOfManufacture' =>$this->pais_fabricacion,
    //                     'harmonizedCode' => $this->categoria,
    //                     'weight' => [
    //                         'units' =>'KG',
    //                         'value' => $bitacoraCotizacionMensajeriaTO->getPeso(),
    //                     ],
    //                     'quantity' =>1,
    //                     'quantityUnits' =>'EA', ///--->Significa cada uno, por pieza
    //                     'unitPrice' =>[
    //                         'currency' => $bitacoraCotizacionMensajeriaTO->getMoneda(),
    //                         'amount' => $bitacoraCotizacionMensajeriaTO->getValorPaquete(),
    //                     ]
    //                 ]
    //             ],
    //             "pickupType" => "CONTACT_FEDEX_TO_SCHEDULE", //preguntar si este es DropoffType::_REGULAR_PICKUP
    //             "serviceType" => $bitacoraCotizacionMensajeriaTO->getTipoServicio(),
    //             "packagingType" => "YOUR_PACKAGING",
    //             "rateRequestType" => ["LIST"],
    //             "totalPackageCount" => 1
    //         ],
    //         "labelResponseOptions" => "LABEL",
    //         "accountNumber" => [
    //             "value" => env('FEDEX_ACCOUNT')
    //         ]
    //     ];

       
    //     //preguntar si este es el seguro en internacionales
    //     if ($bitacoraCotizacionMensajeriaTO->getSeguro()) {
    //         Log::info("Requiere seguro SI");
    //         $data['requestedShipment']['requestedPackageLineItems'][0]['declaredValue'] = [
    //             'amount' => number_format($bitacoraCotizacionMensajeriaTO->getValorPaquete(), 2, '.', ''),
    //             'currency' => 'NMP'
    //         ];
    //     }

    //     $this->internationalData = $data;
    //     $this->endpoint = $this->baseUrl . 'ship/v1/shipments';
    // }

    function validarLongitudCadena($cadena, $longitudMaxima = 50) {
        // Verificar si la longitud de la cadena es mayor que la longitud máxima
        $cadena = $this->removerAcentos($cadena);
        if (strlen($cadena) > $longitudMaxima) {
            // Cortar la cadena a la longitud máxima
            $cadena = substr($cadena, 0, $longitudMaxima);
        }
        return $cadena;
    }

    public function removerAcentos($cadena)
    {
        $cadena = trim($cadena);

        $cadena = str_replace(
            array('á', 'à', 'ä', 'â', 'ª', 'Á', 'À', 'Â', 'Ä'),
            array('a', 'a', 'a', 'a', 'a', 'A', 'A', 'A', 'A'),
            $cadena
        );

        $cadena = str_replace(
            array('é', 'è', 'ë', 'ê', 'É', 'È', 'Ê', 'Ë'),
            array('e', 'e', 'e', 'e', 'E', 'E', 'E', 'E'),
            $cadena
        );

        $cadena = str_replace(
            array('í', 'ì', 'ï', 'î', 'Í', 'Ì', 'Ï', 'Î'),
            array('i', 'i', 'i', 'i', 'I', 'I', 'I', 'I'),
            $cadena
        );

        $cadena = str_replace(
            array('ó', 'ò', 'ö', 'ô', 'Ó', 'Ò', 'Ö', 'Ô'),
            array('o', 'o', 'o', 'o', 'O', 'O', 'O', 'O'),
            $cadena
        );

        $cadena = str_replace(
            array('ú', 'ù', 'ü', 'û', 'Ú', 'Ù', 'Û', 'Ü'),
            array('u', 'u', 'u', 'u', 'U', 'U', 'U', 'U'),
            $cadena
        );

        $cadena = str_replace(
            array('ñ', 'Ñ', 'ç', 'Ç', 'ï¿½','ÃƒÂ±'),
            array('n', 'N', 'c', 'C', 'n', 'n'),
            $cadena
        );

        //Esta parte se encarga de eliminar cualquier caracter extraño
        $cadena = str_replace(
            array("\\", "¨", "º", "-", "~",
                "#", "@", "|", "!", "\"",
                "·", "$", "%", "&", "/",
                "(", ")", "?", "'", "¡",
                "¿", "[", "^", "`", "]",
                "+", "}", "{", "¨", "´",
                ">", "<", ";", ",", ":",
                ".", "ï¿½",'ÃƒÂ±'),
            '',
            $cadena
        );
        return $cadena;
    }
}
