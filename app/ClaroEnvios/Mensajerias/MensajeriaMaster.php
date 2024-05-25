<?php

namespace App\ClaroEnvios\Mensajerias;

use App\ClaroEnvios\Comercios\ConfiguracionesComercios\ConfiguracionComercio;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use SoapClient;
use stdClass;


class MensajeriaMaster
{
    private $costo_mensajeria;
    protected static $options;
    protected static $context;
    protected static $wsdl;

    /**
     * Obtiene el costo total que se cobrara al cliente
     * @param $precioComercio
     * @param $precioT1envios
     * @return float
     */
    public function obtenerCostoTotalTabulador($precioComercio, $precioT1envios = 0){
        $costoAdicional = 0;
        $this->porcentaje_negociacion = 0;

        if ($this->costo != 0) {
            $costoAdicional = $this->costo;
        }elseif ($this->porcentaje != 0){
            $costoAdicional = round($precioComercio * ($this->porcentaje/ 100), 4);
        }
        $costoSeguro = $this->seguro ? round($this->valor_paquete*($this->porcentaje_seguro/100), 4):0;
        $costoTotal = round($precioComercio - $costoAdicional + $costoSeguro, 4);
        $this->costo_mensajeria = $precioT1envios;
//        die(print_r($costoSeguro));

        if($costoTotal <= $precioT1envios){
            if($precioT1envios <= $precioComercio){

                $restante = $precioComercio - $precioT1envios;
                $porcentajeRestante = round(($restante * 100)/ $precioT1envios,2);
                $costoAdicional = round($precioComercio * ($porcentajeRestante/ 100), 4);
                $costoTotal = round($precioComercio - $costoAdicional + $costoSeguro + $this->costo_adicional, 4);

                if ($this->costo != 0) {
                    $this->costo_calculado = $costoAdicional;

                }elseif ($this->porcentaje != 0){
                    $this->porcentaje_calculado = $porcentajeRestante;
                }

            }else{
                $costoTotal = round($precioT1envios + $costoSeguro + $this->costo_adicional, 4);

            }
        }

        return $costoTotal;
    }

    /**
     * Respuesta con datos de cada servicio de una mensajeria
     * @param $costoT1envios
     * @param $costoCliente
     * @param $costoTotalCalaculado
     * @param $tipoServicio
     * @return stdClass
     */
    public function responseService($costoT1envios,$costoCliente,$costoTotalCalaculado,ServicioMensajeria $datosServicio,Carbon $fechaEntrega=null,$costoTotalSeguro=0,$envioInternacional = false, $totalPaquetes = 0){
        Log::info('responseService');
        
        $servicio = new stdClass();
        $servicio->servicio = $datosServicio->nombre;
        $servicio->tipo_servicio = $datosServicio->descripcion;
        
        if ($totalPaquetes>=0) 
            $servicio->total_paquetes = ($totalPaquetes==0) ? 1 : $totalPaquetes;
       
        $servicio->costo_mensajeria = $this->costo_mensajeria ?? $costoT1envios;
        //este solo se utiliza cuando se hacia tariticador con tabuladores y se comparaba el precio de t1envios con el del cliente
        if($this->negociacion_id == 3)
            $servicio->costo_cliente = $costoCliente;
        $servicio->costo_total = $costoTotalCalaculado;
        $fechaActual = new Carbon();
        $fechaEntrega = $fechaEntrega?$fechaEntrega:$this->obtenerFechaEntrega($datosServicio->nombre);
//        die(print_r($fechaEntrega));
        $totalDias = $fechaEntrega->diffInDays($fechaActual);
        $servicio->fecha_mensajeria_entrega = $fechaEntrega->format('Y-m-d');
        $fechaEntregaClaro = $fechaEntrega->copy();
        $fechaEntregaClaro->addDays($this->dias_embarque);
        $servicio->fecha_claro_entrega = $fechaEntregaClaro->format('Y-m-d');
        $servicio->dias_entrega = $totalDias;

        $pesoVolumetrico = round(($this->largo * $this->ancho * $this->alto)/5000) ;

        if($pesoVolumetrico == 0) $pesoVolumetrico = 1;


        $servicio->negociacion_id = $this->negociacion_id;
        $servicio->negociacion = $this->negociacion;
        $servicio->porcentaje_negociacion = $this->porcentaje;
        $servicio->id_configuracion= $this->id_configuracion;
        //die(print_r($this));

        if($this->porcentaje_calculado)
            $servicio->porcentaje_calculado = $this->porcentaje_calculado;
//        $servicio->costo_calculado = $this->costo_calculado ?? $costoTotalCalaculado;

        $servicio->costo_negociacion = $this->costo;
        $servicio->porcentaje_seguro = $this->porcentaje_seguro;
        $servicio->costo_seguro = $this->costo_seguro;
//        die(var_dump($envioInternacional));
        $valorPaquete = $this->valor_paquete;
        if($envioInternacional == true){
            $valorPaquete = $this->valor_paquete*21;
        }
        $servicio->valor_paquete = $valorPaquete;

        if($this->negociacion_id == 3)
            $servicio->costo_adicional = $this->costo_adicional;
        $servicio->moneda = 'MXN';
        $servicio->peso = $this->peso;
        $servicio->peso_volumetrico = $pesoVolumetrico;
        $servicio->peso_unidades = 'KG';
        $servicio->largo = $this->largo;
        $servicio->ancho = $this->ancho;
        $servicio->alto = $this->alto;
        $servicio->costo_zona_extendida = 0;
        $servicio->costo_total_seguro = $costoTotalSeguro;

//        die(print_r($servicio));
        return $servicio;
    }

    public function responseServiceTab($costoT1envios,$costoCliente,$costoTotalCalaculado,ServicioMensajeria $datosServicio,$costoZonaExtendida,Carbon $fechaEntrega=null,$costoTotalSeguro){

        $servicio = new stdClass();
        $servicio->servicio = $datosServicio->nombre;
        $servicio->tipo_servicio = $datosServicio->descripcion;

        $servicio->costo_mensajeria = $this->costo_mensajeria ?? $costoT1envios;
        //este solo se utiliza cuando se hacia tariticador con tabuladores y se comparaba el precio de t1envios con el del cliente
        if($this->negociacion_id == 3)
            $servicio->costo_cliente = $costoCliente;
        $servicio->costo_total = $costoTotalCalaculado;
        $fechaActual = new Carbon();
        $fechaEntrega = $fechaEntrega?$fechaEntrega:$this->obtenerFechaEntrega($datosServicio->nombre);
//        die(print_r($fechaEntrega));
        $totalDias = $fechaEntrega->diffInDays($fechaActual);
        $servicio->fecha_mensajeria_entrega = $fechaEntrega->format('Y-m-d');
        $fechaEntregaClaro = $fechaEntrega->copy();
        $fechaEntregaClaro->addDays($this->dias_embarque);
        $servicio->fecha_claro_entrega = $fechaEntregaClaro->format('Y-m-d');
        $servicio->dias_entrega = $totalDias;

        $pesoVolumetrico = round(($this->largo * $this->ancho * $this->alto)/5000) ;

        if($pesoVolumetrico == 0) $pesoVolumetrico = 1;


        $servicio->negociacion_id = $this->negociacion_id;
        $servicio->porcentaje_negociacion = $this->porcentaje;

        if($this->porcentaje_calculado)
            $servicio->porcentaje_calculado = $this->porcentaje_calculado;
//        $servicio->costo_calculado = $this->costo_calculado ?? $costoTotalCalaculado;

        $servicio->costo_negociacion = $this->costo;
        $servicio->porcentaje_seguro = $this->porcentaje_seguro;
        $servicio->costo_seguro = $this->costo_seguro;
        $servicio->costo_zona_extendida = $costoZonaExtendida;
        $servicio->valor_paquete = $this->valor_paquete;
        if($this->negociacion_id == 3)
            $servicio->costo_adicional = $this->costo_adicional;
        $servicio->moneda = 'MXN';
        $servicio->peso = $this->peso;
        $servicio->peso_volumetrico = $pesoVolumetrico;
        $servicio->peso_unidades = 'KG';
        $servicio->largo = $this->largo;
        $servicio->ancho = $this->ancho;
        $servicio->alto = $this->alto;
        $servicio->costo_total_seguro = $costoTotalSeguro;
//        (print_r($servicio));
        return $servicio;
    }

    public function responseTarificador($mensajeria, $seguro,$comercioId){
        $tarificador = new \stdClass();
        $tarificador->id = $mensajeria->id;
        $tarificador->clave = $mensajeria->clave;
        $tarificador->comercio = $comercioId;
        $tarificador->seguro = $seguro;
       
        return $tarificador;
    }

    public function responseTarificadorTab($mensajeria, $seguro,$comercioId,$comercioIdT1Pginas,$paquete,$comercioPedido, $envioInternacional,$configuracionComercio = 1,$nivel=null){
        $tarificador = new \stdClass();
        $tarificador->id = $mensajeria->id;
        $tarificador->clave = $mensajeria->clave;
        $tarificador->comercio = $comercioId;
        $tarificador->comercio_clave = $comercioIdT1Pginas;

        if (!in_array($configuracionComercio, ConfiguracionComercio::$comerciosZonas))
            $tarificador->paquete = $paquete;

        if (in_array($configuracionComercio, ConfiguracionComercio::$comerciosPrepago)) {
            $tarificador->nivel = $nivel;
        }
         
        $tarificador->seguro = $seguro;
        $tarificador->pedido_comercio = $comercioPedido;
        $tarificador->envio_internacional = $envioInternacional;

        return $tarificador;
    }


    /**
     * Obtiene fecha de entrega de un servicio de una mensajeria
     * @param $tipoServicio
     * @return Carbon
     * @throws \Exception
     */
    public static function obtenerFechaEntrega($tipoServicio){
        $fechaActual = new Carbon();
        $fecha = $fechaActual;

        switch ($tipoServicio) {
            case 'FEDEX_EXPRESS_SAVER':
                $fecha = $fechaActual->addDays(5);
                break;
            case 'STANDARD_OVERNIGHT':
            case 'EXPRESS DOMESTIC':
                $fecha = $fechaActual->addHours(24);
                break;
            case "TWO_DAYS":
            case "ECONOMY SELECT DOMESTIC":
                $fecha = $fechaActual->addDays(2);
                break;
            case "NEXT_DAY":
                $fecha = $fechaActual->addHours(24);
                break;
            case '2_horas':
                $fecha = $fechaActual->addHours(3);
                break;
            default: $fechaActual;

        }

        return $fecha;
    }

    /**
     * Busca el un servicio de mensajeria
     * @param string $mensajeria
     * @param string $servicio
     * @return mixed
     * @throws \Exception
     */
    public function obtenerServicioMensajeria($mensajeria, $servicio){
        Log::info('obtenerServicioMensajeria');
        $serviciosMensajerias = ServicioMensajeria::all();
        $servicioMensajeria = $serviciosMensajerias->where('nombre',$servicio)->first();

        if(!$servicioMensajeria)
            throw new \Exception("No se encontro el servicio: ".$mensajeria.'-'.$servicio);

        return $servicioMensajeria;
    }


    public static function setWsdl($service) {
        return self::$wsdl = $service;
    }
    public static function getWsdl(){
        return self::$wsdl;
    }
    protected static function generateContext(){
        self::$options = [
            'http' => [
                'user_agent' => 'PHPSoapClient'
            ]
        ];
        return self::$context = stream_context_create(self::$options);
    }
    public function loadXmlStringAsArray($xmlString)
    {
        $array = (array) @simplexml_load_string($xmlString);
        if(!$array){
            $array = (array) @json_decode($xmlString, true);
        } else{
            $array = (array)@json_decode(json_encode($array), true);
        }
        return $array;
    }
    public static function initSoapClient(){
        $wsdlUrl = self::getWsdl();
        $soapClientOptions = [
            'stream_context' => self::generateContext(),
            'cache_wsdl'     => WSDL_CACHE_NONE,
            'trace'          => 1,
        ];
        return new SoapClient($wsdlUrl, $soapClientOptions);
    }

}