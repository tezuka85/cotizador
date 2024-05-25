<?php

namespace App\ClaroEnvios\Xml;

use Arcanedev\LogViewer\Entities\Log;
use Illuminate\Support\Facades\Log as FacadesLog;

class Dhl
{
    public static function recoleccion()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <req:BookPickupRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                    xsi:schemaLocation="http://www.dhl.com book-pickup-req.xsd">
                    <Request>
                        <ServiceHeader>
                            <SiteID></SiteID>
                            <Password></Password>
                        </ServiceHeader>
                    </Request>
                    <Requestor>
                        <AccountType></AccountType>
                        <AccountNumber></AccountNumber>
                    </Requestor>
                    <Place>
                        <LocationType></LocationType>
                        <CompanyName></CompanyName>
                        <Address1></Address1>
                        <Address2></Address2>
                        <PackageLocation></PackageLocation>
                        <City></City>
                        <DivisionName></DivisionName>
                        <CountryCode></CountryCode>
                        <PostalCode></PostalCode>
                    </Place>
                    <Pickup>
                        <PickupDate></PickupDate>
                        <ReadyByTime></ReadyByTime>
                        <CloseTime></CloseTime>
                        <Pieces></Pieces>
                        <weight>
                            <Weight></Weight>
                            <WeightUnit></WeightUnit>
                        </weight>
                    </Pickup>
                    <PickupContact>
                        <PersonName></PersonName>
                        <Phone></Phone>
                        <PhoneExtention></PhoneExtention>
                    </PickupContact>
                    <ShipmentDetails>
                        <AccountType></AccountType>
                        <AccountNumber></AccountNumber>      
                        <BillToAccountNumber></BillToAccountNumber>
                        <NumberOfPieces></NumberOfPieces>
                        <Weight></Weight>
                        <WeightUnit></WeightUnit>
                        <DoorTo></DoorTo>
                        <DimensionUnit></DimensionUnit>
                        <Pieces>
                            <Weight></Weight>
                            <Width></Width>
                            <Height></Height>
                            <Depth></Depth>
                        </Pieces>
                    </ShipmentDetails> 
                </req:BookPickupRequest>';
        return $xml;
    }

    public static function tracking($arrayGuias = [0=>''])
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <req:KnownTrackingRequest xmlns:req="http://www.dhl.com" 
                                    xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" 
                                    xsi:schemaLocation="http://www.dhl.com
                                    TrackingRequestKnown.xsd">
                <Request>
                    <ServiceHeader>
                        <MessageTime>2002-06-25T11:28:56-08:00</MessageTime>
                        <MessageReference>1234567890123456789012345678</MessageReference>
                        <SiteID></SiteID>
                        <Password></Password>
                    </ServiceHeader>
                </Request>
                <LanguageCode>es</LanguageCode>';
        foreach ($arrayGuias as $guia) {
            $xml .= "<AWBNumber>{$guia}</AWBNumber>\n";
        }
        $xml .='<LevelOfDetails>ALL_CHECK_POINTS</LevelOfDetails>
                <PiecesEnabled>S</PiecesEnabled> 
            </req:KnownTrackingRequest>';
        return $xml;
    }

    public static function cotizacion($config,$data)
    {
//        die(print_r($data));
        $fechaHeader = gmdate(DATE_ATOM);
        $numPaquetes=$data->get("paquetes");
        $paquetesDetail=$data->get("paquetes_detail");
        $pieces = '';
        if ($paquetesDetail!= null) {
            for ($i=0; $i < count($paquetesDetail); $i++) { 
                $pieceId = $i+1;
                $pieces .= '<Piece>
                        <PieceID>'.$pieceId.'</PieceID>
                        <Height>'.$paquetesDetail[$i]["alto"].'</Height>
                        <Depth>'.$paquetesDetail[$i]["ancho"].'</Depth>
                        <Width>'.$paquetesDetail[$i]["largo"].'</Width>
                        <Weight>'.$paquetesDetail[$i]["peso"].'</Weight>
                    </Piece>';
            }
        }else if ($numPaquetes>1) {
            for ($i=0; $i < $numPaquetes; $i++) { 
                $pieceId = $i+1;
                $pieces .= '<Piece>
                        <PieceID>'.$pieceId.'</PieceID>
                        <Height>'.$data->get("alto").'</Height>
                        <Depth>'.$data->get("ancho").'</Depth>
                        <Width>'.$data->get("largo").'</Width>
                        <Weight>'.$data->get("peso").'</Weight>
                    </Piece>';
            }
        }else{
            $pieces = '<Piece>
                        <PieceID>1</PieceID>
                        <Height>'.$data->get("alto").'</Height>
                        <Depth>'.$data->get("ancho").'</Depth>
                        <Width>'.$data->get("largo").'</Width>
                        <Weight>'.$data->get("peso").'</Weight>
                    </Piece>';
        }
        $xml =  '<?xml version="1.0" encoding="UTF-8"?>
                <req:DCTRequest xmlns:req="http://www.dhl.com" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xsi:schemaLocation="http://www.dhl.com DCT-req.xsd">
                 <GetQuote>
                  <Request>
                   <ServiceHeader>
                    <MessageTime>'.$fechaHeader.'</MessageTime>
                    <MessageReference>1234567890123456789012345678901</MessageReference>
                    <SiteID>'.$config->get("siteId").'</SiteID>
                    <Password>'.$config->get("password").'</Password>
                    </ServiceHeader>
                   </Request>
                  <From>
                   <CountryCode>MX</CountryCode>
                   <Postalcode>'.$data->get("codigo_postal_origen").'</Postalcode>
                  </From>
                  <BkgDetails>
                   <PaymentCountryCode>MX</PaymentCountryCode>
                   <Date>'.$data->get("fecha_liberacion")->format('Y-m-d').'</Date>
                   <ReadyTime>'.$data->get("fecha_liberacion")->format('\P\TH\Hi\Ms\S').'</ReadyTime>
                   <ReadyTimeGMTOffset>'.$data->get("fecha_liberacion")->format('P').'</ReadyTimeGMTOffset>
                   <DimensionUnit>CM</DimensionUnit>
                   <WeightUnit>KG</WeightUnit>
                   <Pieces>'.
                        $pieces
                    .'    
                   </Pieces>
                   <PaymentAccountNumber>'.$config->get("shipperAccountNumber").'</PaymentAccountNumber>
                   <IsDutiable>N</IsDutiable>
                   <NetworkTypeCode>AL</NetworkTypeCode>';

        if($data->get("seguro")==true){
            FacadesLog::info("Requiere seguro DHL SI");
            $xml .=  '
                   <InsuredValue>'.$data->get("valor_paquete").'</InsuredValue>
                   <InsuredCurrency>MXN</InsuredCurrency>';
        }

        $xml .='  
                  </BkgDetails>
                  <To>
                   <CountryCode>MX</CountryCode>
                   <Postalcode>'.$data->get("codigo_postal_destino").'</Postalcode>
                  </To>
                 </GetQuote>
                </req:DCTRequest>';
        
//        die(print_r($xml));
        return $xml;

    }

}