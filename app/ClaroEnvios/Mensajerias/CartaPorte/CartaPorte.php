<?php


App\ClaroEnvios\Mensajerias\CartaPorte;

use App\Models\LogsCartaPorte;

class CartaPorte
{
    public static $noGuia;
    public static $datostienda;
    public static $datoscliente;
    public static $datosProducto;
    public static $urlPorte;
    public static $idMensajeria;
    public static $numpedido;
    public static $tipoguia;

    public function __construct($num_guia,$d_tienda,$d_cliente,$d_productos,$url_porte,$mensajeria,$numero_pedido,$tipo_guia = "normal")
    {
        self::$noGuia        = $num_guia;
        self::$datostienda   = (array)$d_tienda;
        self::$datoscliente  = (array)$d_cliente;
        self::$datosProducto = $d_productos;
        self::$urlPorte      = $url_porte;
        self::$idMensajeria  = $mensajeria;
        self::$numpedido     = $numero_pedido;
        self::$tipoguia      = $tipo_guia;
    }

    public static function cartaPorteFedex()
    {
        try {

            $dtienda         = self::$datostienda;
            $dcliente        = self::$datoscliente;
            $datos_productos = self::$datosProducto;
            $guia            = self::$noGuia;
            $urlporte        = self::$urlPorte;
            $tipo_genera     = self::$tipoguia;

            $invoice_date = date('Y-m-d\TH:i:sO');
            $array_productos = array();
            $peso = 0; $alto=0; $ancho=0; $profundidad=0;
            if(is_array($datos_productos)) {
                foreach ($datos_productos as $key => $value) {
                    $pesoUnitarioProd = (($value->alto * $value->ancho * $value->profundidad) / 5000);
                    error_log("PesoI:".$pesoUnitarioProd);
                    if($pesoUnitarioProd < 0.5)
                    {
                        $pesoUnitarioProd = 0.5;
                    }
                    error_log("PesoF:".$pesoUnitarioProd);
                    $items = array(
                        "bienesTransp"         => 78121500,
                        "descripcion"          => $value->nombre_producto,
                        "cantidad"             => $value->cantidad,
                        "claveUnidad"          => "h87",
                        "unidad"               => "piezas",
                        "dimensiones"          => $value->alto . "/" . $value->ancho . "/" . $value->profundidad . "cm",#"15/30/10cm",""
                        "materialPeligroso"    => "",
                        "cveMaterialPeligroso" => "",
                        "embalaje"             => "Tu",
                        "descripEmbalaje"      => "cajas de carton",
                        "pesoEnKg"             => $pesoUnitarioProd,#"0.200",
                        "valorMercancia"       => $value->precio_producto,
                        "moneda"               => "MXN",
                        "fraccionArancelaria"  => "",
                        "uuidComercioExt"      => ""
                    );
                    array_push($array_productos, $items);
                }
            }

            //Devolucion
            if($tipo_genera == 'devolucion') {
                $ubicaciones = array(
                    "ubicacion" => array(
                        array(
                            "origen" => array(
                                "idOrigen"         => "OR" . $dcliente['num_pedido'],
                                "rfcRemitente"     => "XAXX010101000",
                                "nombreRemitente"  => $dcliente['nombre_completo'],
                                "numRegIdTrib"     => "",
                                "residenciaFiscal" => "MEX",
                                "fechaHoraSalida"  => $invoice_date
                            ),
                            "domicilio" => array(
                                "calle"           => $dcliente['direccion'],
                                "numeroExterior"  => $dcliente['numero_exterior'],
                                "numeroInterior"  => $dcliente['numero_interior'],
                                "colonia"         => $dcliente['colonia'],
                                "localidad"       => "",
                                "referencia"      => $dcliente['observaciones'],
                                "municipio"       => $dcliente['municipio'],
                                "estado"          => $dcliente['estado'],
                                "pais"            => "MEX",
                                "codigoPostal"    => $dcliente['cp']
                            )
                        ),
                        array(
                            "destino" => array(
                                "idOrigen"           => "DE" . $dtienda['num_tienda'],
                                "rfcDestinatario"    => "SOM101125UEA",
                                "nombreDestinatario" => $dtienda['nombre_tienda'],
                                "numRegIdTrib"       => "",
                                "residenciaFiscal"   => "MEX",
                                "fechaHoraSalida"    => $invoice_date
                            ),
                            "domicilio" => array(
                                "calle"          => $dtienda['direccion_tienda'],
                                "numeroExterior" => $dtienda['no_exterior'],
                                "numeroInterior" => "",
                                "colonia"        => $dtienda['colonia_tienda'],
                                "localidad"      => "",
                                "referencia"     => "",
                                "municipio"      => $dtienda['ciudad_tienda'],
                                "estado"         => $dtienda['codigo_estado_tienda'],
                                "pais"           => "MEX",
                                "codigoPostal"   => $dtienda['codigo_postal_tienda']
                            )
                        )
                    )
                );
            } else {
                $ubicaciones = array(
                    "ubicacion" => array(
                        array(
                            "origen" => array(
                                "idOrigen"         => "OR" . $dtienda['num_tienda'],
                                "rfcRemitente"     => "SOM101125UEA",
                                "nombreRemitente"  => $dtienda['nombre_tienda'],
                                "numRegIdTrib"     => "",
                                "residenciaFiscal" => "MEX",
                                "fechaHoraSalida"  => $invoice_date
                            ),
                            "domicilio" => array(
                                "calle"          => $dtienda['direccion_tienda'],
                                "numeroExterior" => $dtienda['no_exterior'],
                                "numeroInterior" => "",
                                "colonia"        => $dtienda['colonia_tienda'],
                                "localidad"      => "",
                                "referencia"     => "",
                                "municipio"      => $dtienda['ciudad_tienda'],
                                "estado"         => $dtienda['codigo_estado_tienda'],
                                "pais"           => "MEX",
                                "codigoPostal"   => $dtienda['codigo_postal_tienda']
                            )
                        ),
                        array(
                            "destino" => array(
                                "idOrigen"           => "DE" . $dcliente['num_pedido'],
                                "rfcDestinatario"    => "XAXX010101000",
                                "nombreDestinatario" => $dcliente['nombre_completo'],
                                "numRegIdTrib"       => "",
                                "residenciaFiscal"   => "MEX",
                                "fechaHoraSalida"    => $invoice_date
                            ),
                            "domicilio" => array(
                                "calle"          => $dcliente['direccion'],
                                "numeroExterior" => $dcliente['numero_exterior'],
                                "numeroInterior" => $dcliente['numero_interior'],
                                "colonia"        => $dcliente['colonia'],
                                "localidad"      => "",
                                "referencia"     => $dcliente['observaciones'],
                                "municipio"      => $dcliente['municipio'],
                                "estado"         => $dcliente['estado'],
                                "pais"           => "MEX",
                                "codigoPostal"   => $dcliente['cp']
                            )
                        )
                    )
                );
            }

            $mercancias   = array(
                "numTotalMercancias" => count($datos_productos),
                "mercancia" => $array_productos
            );

            $comment = "JSON Standar File to receive FedEx customer information and create the Carta Porte requested by SAT (Mexican Authority TAX) Values just as an exmaple, please remove these and use your own values ";

            $result = array(
                "comment" => $comment,
                "customerCartaPorteFDX" => array(
                    "guia" => $guia,
                    "ubicaciones" => $ubicaciones,
                    "mercancias"  => $mercancias
                )
            );

            $data_string = json_encode($result);
            //echo $data_string;
            error_log('CartaPorte-request: '.$data_string);

            $ch = curl_init($urlporte);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            $result = json_decode($response, true);
            error_log('CartaPorte-response: '.json_encode($result));

            $log = new LogsCartaPorte();
            $log->numero_pedido = self::$numpedido;
            $log->request       = $data_string;
            $log->respuesta     = json_encode($result);
            $log->save();

            if($result["status"] == 200 && $httpcode == 200){
                $resultporte = $result["status"]."-".$result["message"]."-".$result["awbNumber"];
            } else {
                $resultporte = $result["status"]."- Error - ".$result["message"];
            }

            return $resultporte;

        }catch (\Exception $e){
            error_log("Catch-error: ".$e->getMessage());
            return $res = (object)array(
                "estatus"   => "ERROR",
                "msj" => "Error-Fedex",
                "Excepcion" => $e->getMessage()
            );
        }

    }

    public static function cartaPorteBeta()
    {
        try {
            $dtienda         = self::$datostienda;
            $dcliente        = self::$datoscliente;
            $datos_productos = self::$datosProducto;
            $guia            = self::$noGuia;
            $urlporte        = self::$urlPorte;

            $invoice_date = date('Y-m-d\TH:i:s');
            $array_productos = array();
            $peso = 0; $alto=0; $ancho=0; $profundidad=0;
            $pesoUnitarioProd = (($datos_productos['alto'] * $datos_productos['ancho'] * $datos_productos['profundidad']) / 5000);
            $peso =  $peso + $pesoUnitarioProd;
            $items = array(
                "viajeID"                => "VIAJE".$datos_productos['relacion_pedido'],
                "pedidoId"               => $datos_productos['num_pedido'],
                "cantidad"               => $datos_productos['cantidad'],
                "claveProdServ"          => 78121500,#revisar
                "claveUnidada"           => "E48",#revisar
                "descripcion"            => $datos_productos['nombre_producto'],
                "materialPeligroso"      => 0,
                "materialPeligrosoCod"   => "",
                "pesoKg"                 => $pesoUnitarioProd,#"0.200",
                "embalaje"               => "Tu",#revisar
                "valorUnitarioMercancia" => $datos_productos['precio_producto']
            );
            array_push($array_productos, $items);


            $result = array(
                "rfcReceptor"        => "",
                "rfcOperador"        => "",
                "placasOperador"     => "",
                "fechaHoraSalida"    => $invoice_date,
                "totalDistRecorrida" => "",#0.507244,#revisar
                "idubicacionSalida"  => $dtienda['num_tienda'],
                "fechaHoraLlegada"   => $invoice_date,
                "idubicacionDestino" => "",
                "nombreOrigen"       => $dtienda['nombre_tienda'],
                "calleOrigen"        => $dtienda['direccion_tienda'],
                "numExtOrigen"       => $dtienda['no_exterior'],
                "cpOrigen"           => $dtienda['codigo_postal_tienda'],
                "coloniaOrigen"      => $dtienda['colonia_tienda'],
                "estadoOrigen"       => $dtienda['codigo_estado_tienda'],
                "localidadOrigen"    => "",
                "municipioOrigen"    => "",
                "municipioDestino"   => "",
                "paisOrigen"         => "MEX",
                "pesoBrutoTotal"     => $peso,
                "numTotalMercancias" => count($datos_productos),
                "cpDestino"          => $dcliente['cp'],
                "rfcDestinatario"    => "XAXX010101000",
                "nombreOperador"     => "",
                "paisDestino"        => "",
                "viajeID"            => "VIAJE".$datos_productos['relacion_pedido'],
                "estadoDestino"      => $dcliente['estado'],
                "coloniaDestino"     => $dcliente['colonia'],
                "localidadDestino"   => "",
                "numExtDestino"      => $dcliente['numero_exterior'],
                "pesoNetoTotal"      => "",
                "rfcRemitente"       => "SOM101125UEA",
                "valorMercancia"     => "",
                "calleDestino"       => $dcliente['direccion'],
                "nombreDestino"      => $dcliente['nombre_completo'],
                "mercancias"         => $array_productos
            );

            $data_string = json_encode($result);
            //echo $data_string;
            error_log('CartaPorte-request: '.$data_string);

            $ch = curl_init($urlporte);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "POST");
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    'Content-Type: application/json',
                    'Content-Length: ' . strlen($data_string))
            );

            $response = curl_exec($ch);
            $httpcode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);
            //echo 'HTTP code: ' . $httpcode;
            $result = json_decode($response, true);
            error_log('CartaPorte-response: '.json_encode($result));

            $log = new LogsCartaPorte();
            $log->numero_pedido = self::$numpedido;
            $log->request       = $data_string;
            $log->respuesta     = json_encode($result);
            $log->save();

            if($httpcode == 500 && isset($result["error"])){
                $resultporte = $result["mensaje"]." - Error - ".$result["error"];
            } else {
                $resultporte = "ID:".$result["Data"]["id"]."-".$result['mensaje'];
            }

            return $resultporte;

        }catch (\Exception $e){
            error_log("Catch-error: ".$e->getMessage());
            return $res = (object)array(
                "estatus"   => "ERROR",
                "msj" => "Error-Fedex",
                "Excepcion" => $e->getMessage()
            );
        }

    }

}
