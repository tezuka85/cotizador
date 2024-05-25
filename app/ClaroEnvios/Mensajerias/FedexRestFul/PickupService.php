<?php

namespace App\ClaroEnvios\Mensajerias\FedexRestFul;

use App\ClaroEnvios\Mensajerias\Bitacora\BitacoraMensajeriaOrigenTO;
use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\Recoleccion\MensajeriaRecoleccionTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class PickupService extends FedexRestFul
{
    public function __construct()
    {
       
    }

    public function setData(MensajeriaRecoleccionTO $mensajeriaRecoleccionTO)
    {
       
        $datos = $mensajeriaRecoleccionTO->getDatos();

        if(array_key_exists('fecha',$datos)){
          $horaInicio=  new Carbon($datos['fecha']);
          $hora = $horaInicio->addHour()->format('H:i:s');
          $diaPickup = new Carbon($datos['fecha'].' '.$hora);
          $fechaFin = new Carbon($datos['horario_cierre']); 
        }else{
          $diaPickup = diaRecoleccion();
          $fechaFin = new Carbon('19:00:00'); 
        }

        $data = [
            'associatedAccountNumber' => [
                'value' => env('FEDEX_ACCOUNT')
              ],
              'originDetail' => [
                'pickupAddressType' => 'SHIPPER',
                'pickupLocation' => [
                  'contact' => [
                    'companyName' =>  'T1 Envios',
                    'personName' => $datos['nombre_contacto'].' '.$datos['apellidos_contacto'],
                    'phoneNumber' => $datos['telefono']
                  ],
                  'address' => [
                    'streetLines' => [$datos['calle'].' '.$datos['numero'].' '.$datos['colonia']],
                    'city' => $datos['estado'],
                    'stateOrProvinceCode' => $mensajeriaRecoleccionTO->getSiglasCodigoOrigen(),
                    'postalCode' => $datos['codigo_postal'],
                    'countryCode' => 'MX'
                  ],
                  'accountNumber' => [
                    'value' => env('FEDEX_ACCOUNT')
                  ]
                ],
                'readyDateTimestamp' => $diaPickup->format('Y-m-d\T12:i:sP'),
                'customerCloseTime' => $fechaFin->format('H:i:s'),
                'packageLocation' => 'FRONT',
                'buildingPart' => 'SUITE',
                'buildingPartDescription' => 'Building part description',
                'suppliesRequested' => 'Supplies requested'
              ],
              'totalWeight' => [
                'units' => 'KG',
                'value' => $datos['peso']
              ],
              'packageCount' => $datos['cantidad_piezas'],
              'carrierCode' => 'FDXE',
              'countryRelationships' => 'DOMESTIC',
              'commodityDescription' => 'This field contains CommodityDescription',
              'oversizePackageCount' => 0
            ];
            Log::info(json_encode($data));

        $this->data = $data;
        $this->endpoint = $this->baseUrl . 'pickup/v1/pickups';
    }

    public function getData(){
        return $this->data;
    }
    
}
