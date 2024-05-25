<?php

namespace App\ClaroEnvios\Mensajerias\FedexRestFul;

use Illuminate\Support\Facades\Log;

class TrackService extends FedexRestFul
{
    
    public function __construct()
    {
    }

    public function setDataFedex($guia,$detailScan)
    {
        //Log::info($detailScan);
        $data = [
            'trackingInfo' => [
                [
                    'trackingNumberInfo' => [
                        'trackingNumber' => $guia,
                    ]
                ]   
            ],
            'includeDetailedScans' => $detailScan
        ];

        $this->data = $data;
       
        //Log::info(json_encode($data));
    }

    public function getDataFedex()
    {
        return $this->data;
    }
    
    
}
