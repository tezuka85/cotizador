<?php

namespace App\ClaroEnvios\Mensajerias\FedexRestFul;

use App\ClaroEnvios\Mensajerias\FedexRF\Rate\RequestedShipment;
use Illuminate\Support\Facades\Log;

class RateService extends FedexRestFul
{
    public function __construct()
    {
       
    }

    public function setData($dataRequest)
    {
        // die(print_r($dataRequest));
        if (env('API_LOCATION') == 'test' || env('API_LOCATION') == 'desarrollo') { //Descomentar para ambientes
            Log::info('setData FEDEX DEV');
            $data = [
                "accountNumber" => [
                    "value" => "740561073"
                ],
                "carrierCodes" => [
                    "FDXE"
                ],
                "requestedShipment" => [
                    "shipper" => [
                        "address" => [
                            "postalCode" =>  $dataRequest['codigo_postal_destino'],
                            "countryCode" => "MX"
                        ]
                    ],
                    "recipient" => [
                        "address" => [
                            "postalCode" => $dataRequest['codigo_postal_origen'],
                            "countryCode" => "MX"
                        ]
                    ],
                    "shipmentSpecialServices" => [
                        "specialServiceTypes" => [
                            "SATURDAY_PICKUP"
                        ]
                    ],
                    "shipDateStamp" =>  now()->format('Y-m-d'),
                    "pickupType" => "USE_SCHEDULED_PICKUP",
                    "packagingType" => "YOUR_PACKAGING",
                    "rateRequestType" => [
                        "PREFERRED",
                        "LIST"
                    ],
                    "requestedPackageLineItems" => [
                        [
                            "weight" => [
                                "units" => "KG",
                                "value" => $dataRequest['peso_calculado']
                            ]
                        ]
                    ]
                ]
            ];

        }else{
            Log::info('setData FEDEX Prod');

            $packages = 1;
            if ($dataRequest['paquetes']>1) 
                $packages = $dataRequest['paquetes'];
            
            $packagesDetail = [];
            if ($dataRequest['paquetes_detail'] != null) {
                
                foreach($dataRequest['paquetes_detail'] as $paquete){
                    array_push($packagesDetail,    
                        [
                            "weight"=> [
                                    "units" =>"KG",
                                    "value"=> $paquete['peso']
                            ],
                            "dimensions"=> [
                                    "length"=> $paquete['largo'],
                                    "width"=> $paquete['ancho'],
                                    "height"=> $paquete['alto'],
                                    "units"=> "CM"
                            ],
                            "groupPackageCount"=> 1,
                                
                        ]
                    );
                }
            } else {
                $packagesDetail = [
                    [
                        "weight"=> [
                                "units" =>"KG",
                                "value"=> $dataRequest['peso_calculado']
                        ],
                        "dimensions"=> [
                                "length"=> $dataRequest['largo'],
                                "width"=> $dataRequest['ancho'],
                                "height"=> $dataRequest['alto'],
                                "units"=> "CM"
                        ],
                        "groupPackageCount"=> $packages,
                                
                        ]
                ];
            }

        
            $data = [
                "accountNumber" => ["value" => env('FEDEX_ACCOUNT')],
                'requestedShipment' => [
                    'shipDatestamp' => now()->format('Y-m-d'),

                    "preferredCurrency"=>$dataRequest['moneda'],
                    'shipper' => $this->dataShipper($dataRequest['siglas_codigo_postal_origen'],$dataRequest['codigo_postal_origen']),
                    'recipient' => [
                        'address' => [
                            'stateOrProvinceCode' => $dataRequest['siglas_codigo_postal_destino'],
                            'postalCode' => $dataRequest['codigo_postal_destino'],
                            'countryCode' => 'MX'
                            
                        ]
                    ],
                    "pickupType"=>"USE_SCHEDULED_PICKUP",
                    "rateRequestType"=>[
                        "PREFERRED",
                        "LIST"
                        ],
                    'requestedPackageLineItems' => $packagesDetail,

                    "totalPackageCount" => $packages

                ]
            ];
        }
        //preguntar si este es el seguro
        if($dataRequest['seguro']){
            Log::info("Requiere seguro SI");
            if($dataRequest['moneda'] == 'MXN'){
                $dataRequest['moneda'] = 'NMP'; //nuevos pesos mexicanos (npi por qué sea así, pero así lo pide fedex)
            }
            
            $declaredValue = [
                "declaredValue" => [
                    "amount" => $dataRequest['valor_paquete'],
                    "currency" => $dataRequest['moneda']
                ]
            ];

            $data["requestedShipment"]["requestedPackageLineItems"][0] = array_merge($data["requestedShipment"]["requestedPackageLineItems"][0], $declaredValue); 
            
        }
        Log::info("REQUEST RATE FEDEX:");
        Log::info($data);
        //dd(json_encode($data));
    
        $this->data = $data;
        $this->endpoint = $this->baseUrl . 'rate/v1/rates/quotes';
    }

    private function dataShipper($siglasEstado,$cpOrigen) {
        $data = [
            'address' => [
                'stateOrProvinceCode' => $siglasEstado,
                'postalCode' => $cpOrigen,
                'countryCode' => 'MX'
            ]
        ];

        return $data;
    }

    public function getData(){
        return $this->data;
    }
    
}
