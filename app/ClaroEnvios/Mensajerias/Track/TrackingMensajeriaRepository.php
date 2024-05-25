<?php

namespace App\ClaroEnvios\Mensajerias\Track;


use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeriaTO;
use App\ClaroEnvios\Mensajerias\GuiaMensajeriaTO;
use App\ClaroEnvios\Mensajerias\MensajeriaTO;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

/**
 * Class TrackingMensajeriaRepository
 * @package App\ClaroEnvios\Mensajerias\Track
 */
class TrackingMensajeriaRepository
{
    public function buscaGuiasLista(TrackingMensajeriaTO $trackingMensajeriaTO)
    {
        $trackingMensajeria = TrackingMensajeria::query();
        $trackingMensajeria->when($trackingMensajeriaTO->getId(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('_id', $trackingMensajeriaTO->getId());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getIdSeller(), function ($query) use ($trackingMensajeriaTO) {
            //            die(print_r($trackingMensajeriaTO->getIdSeller()));
            $query->where('id_seller', $trackingMensajeriaTO->getIdSeller());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getGuia(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('guia', $trackingMensajeriaTO->getGuia());
        });

        //        die(print_r($trackingMensajeriaTO->getFechaInicio()->format('Y-m-d H:i:s')));
        $trackingMensajeria->when($trackingMensajeriaTO->getCodigo(), function ($query) use ($trackingMensajeriaTO) {
            $query->whereIn('codigo', $trackingMensajeriaTO->getCodigo());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getFechaInicio(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('fecha_hora', '>=', $trackingMensajeriaTO->getFechaInicio()->startOfDay());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getFechaFin(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('fecha_hora', '<=', $trackingMensajeriaTO->getFechaFin()->endOfDay());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getIdMensajeria(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('id_mensajeria', $trackingMensajeriaTO->getIdMensajeria());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getNumOrden(), function ($query) use ($trackingMensajeriaTO) {
            //            die($trackingMensajeriaTO->getNumOrden());
            $query->where('num_orden', $trackingMensajeriaTO->getNumOrden());
        });
        //        Log::info($trackingMensajeria->get());
        $total = $trackingMensajeria->count();
        $trackingMensajeria
            ->skip($trackingMensajeriaTO->getLimit() * $trackingMensajeriaTO->getPage())
            ->limit($trackingMensajeriaTO->getLimit())
            ->orderBy($trackingMensajeriaTO->getColumna(), $trackingMensajeriaTO->getOrder())
            ->get();

        //        die(print_r($total));
        return collect(['data' => $trackingMensajeria->get(), 'total' => $total, 'filtrados' => $trackingMensajeria->get()->count()]);
    }

    
    
    public function buscaUltimoEstatus(TrackingMensajeriaTO $trackingMensajeriaTO)
    {
        Log::info('buscaUltimoEstatus comercio: ' . Auth::user()->comercio_id);
        $guiasMysql =  $trackingMensajeriaTO->getGuias();
        Log::info('guias mysql');
        Log::info($guiasMysql);

        $pipeline = [
            [
                '$match' => [
                    'guia' => ['$in' => $guiasMysql->toArray()],
                ],
            ],
        ];

        $pipeline[] = [
            '$group' => [
                '_id' => '$guia',
                // 'last_id' => ['$first' => '$_id'],
                'num_orden' => ['$last' => '$num_orden'],
                'id_mensajeria' => ['$last' => '$id_mensajeria'],
                'familia_externa' => ['$last' => '$familia_externa'],
                'familia_interna' => ['$last' => '$familia_interna'],
                'descripcion' => ['$last' => '$descripcion'],
                'fecha_alta' => ['$last' => '$fecha_alta'],
                'fecha_hora' => ['$last' => '$fecha_hora'],
                'codigo' => ['$last' => '$codigo'],
                'g_ret' => ['$last' => '$g_ret'],
            ],
        ];

       // die(print_r($trackingMensajeriaTO));
        // Agregar filtro de familia_externa si se proporciona
        if ($trackingMensajeriaTO->getFamiliaExterna()) {
           
            Log::info('getFamiliaExterna: ' . $trackingMensajeriaTO->getFamiliaExterna());
            $pipeline[] = [
                '$match' => ['familia_externa' => $trackingMensajeriaTO->getFamiliaExterna()],
            ];
        }

        // if ($trackingMensajeriaTO->getLimit()) {
        //     Log::info('limit : ' . $trackingMensajeriaTO->getLimit());
        //     $pipeline[] = ['$limit' => $trackingMensajeriaTO->getLimit()];
        // }

        $trackingMensajeria = TrackingMensajeria::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        Log::info('Guias ultimo estatus comercio: ' . Auth::user()->comercio_id);
        Log::info($trackingMensajeria);
        //die(print_r($trackingMensajeria->toArray()));
        return $trackingMensajeria;
    }

    public function buscaUltimoEstatusLista(TrackingMensajeriaTO $trackingMensajeriaTO)
    {
        Log::info('buscaUltimoEstatus comercio: ' . Auth::user()->comercio_id);
        $guiasMysql =  $trackingMensajeriaTO->getGuias();
        Log::info('guias mysql');
        Log::info($guiasMysql);

        $pipeline = [
            [
                '$match' => [
                    'guia' => ['$in' => $guiasMysql->toArray()],
                ],
            ],
        ];

        $pipeline[] = [
            '$group' => [
                '_id' => '$guia',
                // 'last_id' => ['$first' => '$_id'],
                'num_orden' => ['$last' => '$num_orden'],
                'id_mensajeria' => ['$last' => '$id_mensajeria'],
                'familia_externa' => ['$last' => '$familia_externa'],
                'familia_interna' => ['$last' => '$familia_interna'],
                'descripcion' => ['$last' => '$descripcion'],
                'fecha_alta' => ['$last' => '$fecha_alta'],
                'fecha_hora' => ['$last' => '$fecha_hora'],
                'codigo' => ['$last' => '$codigo'],
                'g_ret' => ['$last' => '$g_ret'],
            ],
        ];

        //die(print_r($trackingMensajeriaTO->getPage()));
        // Agregar filtro de familia_externa si se proporciona
        if ($trackingMensajeriaTO->getFamiliaExterna()) {
            Log::info('getFamiliaExterna: ' . $trackingMensajeriaTO->getFamiliaExterna());
            $pipeline[] = [
                '$match' => ['familia_externa' => $trackingMensajeriaTO->getFamiliaExterna()],
            ];
        }

        if ($trackingMensajeriaTO->getLimit()) {
            Log::info('limit : ' . $trackingMensajeriaTO->getLimit());
            $pipeline[] = ['$limit' => $trackingMensajeriaTO->getLimit()];
        }

         // Agregar offset
    // if ($trackingMensajeriaTO->getPage() >= 0) {
    //     $offset = $trackingMensajeriaTO->getLimit() * ($trackingMensajeriaTO->getPage());
    //     Log::info('offset : ' . $offset );

    //    // die(print_r($offset));
    //     $pipeline[] = ['$skip' => $trackingMensajeriaTO->getPage()];
    // }


        $trackingMensajeria = TrackingMensajeria::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });

        Log::info('Guias ultimo estatus comercio: ' . Auth::user()->comercio_id);
        Log::info($trackingMensajeria);
        //die(print_r($trackingMensajeria->toArray()));
        return $trackingMensajeria;
    }

    public function ultimoEstatusPaginado(TrackingMensajeriaTO $trackingMensajeriaTO, $idComercio)
    {
        $pipeline[] = [
            '$match' => ['id_seller' => $trackingMensajeriaTO->getIdSeller()],
        ];

        if ($trackingMensajeriaTO->getGuias()) {
            Log::info('getGuias: ' . $trackingMensajeriaTO->getGuias());
            $pipeline = [
                [
                    '$match' => [
                        'guia' => ['$in' => $trackingMensajeriaTO->getGuias()->toArray()],
                    ],
                ],
            ];
        }

        if ($trackingMensajeriaTO->getGuia()) {
            Log::info('guia: ' . $trackingMensajeriaTO->getGuia());
            $pipeline[] = [
                '$match' => ['guia' => $trackingMensajeriaTO->getGuia()],
            ];
        }
        $pipeline[] = [
            '$group' => [
                '_id' => '$guia',
                // 'last_id' => ['$first' => '$_id'],
                'num_orden' => ['$last' => '$num_orden'],
                'id_mensajeria' => ['$last' => '$id_mensajeria'],
                'familia_externa' => ['$last' => '$familia_externa'],
                'familia_interna' => ['$last' => '$familia_interna'],
                'descripcion' => ['$last' => '$descripcion'],
                'fecha_alta' => ['$last' => '$fecha_alta'],
                'fecha_hora' => ['$last' => '$fecha_hora'],
                'codigo' => ['$last' => '$codigo'],
                'g_ret' => ['$last' => '$g_ret'],
            ],
        ];

        // Agregar ordenación por fecha_alta descendente
        $pipeline[] = ['$sort' => ['fecha_alta' => -1]];
        $trackingMensajeria = TrackingMensajeria::raw(function ($collection) use ($pipeline) {
            return $collection->aggregate($pipeline);
        });
     
        return $trackingMensajeria;
    }


    public function buscaGuias(TrackingMensajeriaTO $trackingMensajeriaTO)
    {
        $trackingMensajeria = TrackingMensajeria::select('guia', 'fecha_hora', 'fecha_alta', 'costo_porcentaje_cotizacion', 'num_orden');
        $trackingMensajeria->when($trackingMensajeriaTO->getId(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('_id', $trackingMensajeriaTO->getId());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getIdSeller(), function ($query) use ($trackingMensajeriaTO) {
            //            die(print_r($trackingMensajeriaTO->getIdSeller()));
            $query->where('id_seller', $trackingMensajeriaTO->getIdSeller());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getGuia(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('guia', $trackingMensajeriaTO->getGuia());
        });

        //        die(print_r($trackingMensajeriaTO->getFechaInicio()));
        $trackingMensajeria->when($trackingMensajeriaTO->getCodigo(), function ($query) use ($trackingMensajeriaTO) {
            $query->whereIn('codigo', $trackingMensajeriaTO->getCodigo());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getFechaInicio(), function ($query) use ($trackingMensajeriaTO) {
            //            die('aqui');
            $query->where('fecha_hora', '>=', $trackingMensajeriaTO->getFechaInicio()->startOfDay());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getFechaFin(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('fecha_hora', '<=', $trackingMensajeriaTO->getFechaFin()->endOfDay());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getIdMensajeria(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('id_mensajeria', $trackingMensajeriaTO->getIdMensajeria());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getNumOrden(), function ($query) use ($trackingMensajeriaTO) {
            //            die($trackingMensajeriaTO->getNumOrden());
            $query->where('num_orden', $trackingMensajeriaTO->getNumOrden());
        });

        $total = $trackingMensajeria->count();
        $trackingMensajeria
            ->groupBy('guia')
            ->orderBy('fecha_alta', 'desc')
            ->get();
        //        Log::info(json_encode($trackingMensajeria->get()));
        //        die(print_r($total));
        return collect(['data' => $trackingMensajeria->get(), 'total' => $total, 'filtrados' => $trackingMensajeria->get()->count()]);
    }


    public function buscaUltimoEstatusReporte(TrackingMensajeriaTO $trackingMensajeriaTO)
    {
        $trackingMensajeria = TrackingMensajeria::select(
            'num_orden',
            'id_seller',
            'id_mensajeria',
            'codigo',
            'guia',
            'familia_externa',
            'notificaciones',
            'fecha_hora',
            'tracking_link',
            'id_estatus',
            'nombre_destino',
            'apellido_destino',
            'email_destino',
            'calle_destino',
            'numero_destino',
            'colonia_destino',
            'municipio_destino',
            'telefono_destino',
            'estado_destino',
            'referencia_destino',
            'numero_externo',
            'codigo_postal_destino'
        );

        $trackingMensajeria->distinct()->groupBy('guia')
            ->orderBy('fecha_hora', 'desc');

        $trackingMensajeria->when($trackingMensajeriaTO->getIdSeller(), function ($query) use ($trackingMensajeriaTO) {
            //            die(print_r($trackingMensajeriaTO->getIdSeller()));
            $query->where('id_seller', $trackingMensajeriaTO->getIdSeller());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getGuia(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('guia', $trackingMensajeriaTO->getGuia());
        });


        $trackingMensajeria->when($trackingMensajeriaTO->getFechaInicio(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('fecha_hora', '>=', $trackingMensajeriaTO->getFechaInicio()->startOfDay());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getFechaFin(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('fecha_hora', '<=', $trackingMensajeriaTO->getFechaFin()->endOfDay());
        });


        $trackingMensajeria->orderBy($trackingMensajeriaTO->getColumna(), $trackingMensajeriaTO->getOrder());
        //        die(print_r($trackingMensajeria->get()));

        $guias = $trackingMensajeria->get();
        //        die(prin  t_r($guias));
        //        Log::info($trackingMensajeria->get());


        return collect(['data' => $guias]);
    }

    public function guardaTrackingMensajeria(MensajeriaTO $mensajeriaTO, GuiaMensajeriaTO $guiaMensajeriaTO, BitacoraCotizacionMensajeriaTO $bitacoraCotizacionMensajeriaTO, $mensajeriaResponse)
    {
        Log::info('Busca CatalogoFamilia: ' . $mensajeriaResponse['infoExtra']['codigo']);
        $catalogoFamilia = CatalogoFamilia::where('codigo', $mensajeriaResponse['infoExtra']['codigo'])->where('id_mensajeria', $mensajeriaTO->getId())
            ->firstOrFail();

        $trackingLink = env('TRACKING_LINK_T1ENVIOS');
        $guiaTrackingMongo = new TrackingMensajeria();
        $guiaTrackingMongo->id_estatus = 0;
        $guiaTrackingMongo->num_orden = $guiaMensajeriaTO->getId();
        $guiaTrackingMongo->id_seller = $guiaMensajeriaTO->getComercioId();
        $guiaTrackingMongo->id_mensajeria = $mensajeriaTO->getId();
        $guiaTrackingMongo->codigo = strval($mensajeriaResponse['infoExtra']['codigo']);
        $guiaTrackingMongo->guia = strval($mensajeriaResponse['guia']);
        $guiaTrackingMongo->descripcion = "Guía generada";
        $guiaTrackingMongo->fecha_hora = $mensajeriaResponse['infoExtra']['fecha_hora'];
        $guiaTrackingMongo->identificadorUnico = $mensajeriaResponse['infoExtra']['identificadorUnico'];
        $guiaTrackingMongo->nombre_recibe = "";
        $guiaTrackingMongo->nombre_cliente = "";
        $guiaTrackingMongo->fecha_estimada = $bitacoraCotizacionMensajeriaTO->getFechaEntregaClaro();
        $guiaTrackingMongo->peso_paquete = '';
        $guiaTrackingMongo->unidad_peso_paquete = '';
        $guiaTrackingMongo->peso_envio = '';
        $guiaTrackingMongo->unidad_peso_envio = '';
        $guiaTrackingMongo->largo = "";
        $guiaTrackingMongo->alto = "";
        $guiaTrackingMongo->ancho = "";
        $guiaTrackingMongo->unidad_dimesiones = '';
        $guiaTrackingMongo->tracking_link = $mensajeriaResponse['infoExtra']['tracking_link'];
        $guiaTrackingMongo->tracking_link_t1envios = $trackingLink . $mensajeriaResponse['guia'];
        $guiaTrackingMongo->familia_externa = $catalogoFamilia->estatus_externo;
        $guiaTrackingMongo->familia_interna = $catalogoFamilia->estatus_interno;
        $guiaTrackingMongo->notificaciones = intval($guiaMensajeriaTO->getNotificacion());
        $guiaTrackingMongo->fecha_alta = Carbon::now();
        $guiaTrackingMongo->fecha_modificacion = Carbon::now();
        $guiaTrackingMongo->valorPaquete_cotizacion = $bitacoraCotizacionMensajeriaTO->getValorPaquete();
        $guiaTrackingMongo->costo_porcentaje_cotizacion = $bitacoraCotizacionMensajeriaTO->getCostoTotal();
        $destino = $guiaMensajeriaTO->getBitacoraMensajeriaDestinoTO();
        $guiaTrackingMongo->nombre_destino = $destino->getNombre();
        $guiaTrackingMongo->apellido_destino = $destino->getApellidos();
        $guiaTrackingMongo->email_destino = $destino->getEmail();
        $guiaTrackingMongo->calle_destino = $destino->getCalle();
        $guiaTrackingMongo->numero_destino = $destino->getNumero();
        $guiaTrackingMongo->colonia_destino = $destino->getColonia();
        $guiaTrackingMongo->municipio_destino = $destino->getMunicipio();
        $guiaTrackingMongo->telefono_destino = $destino->getTelefono();
        $guiaTrackingMongo->estado_destino = $destino->getEstado();
        $guiaTrackingMongo->referencia_destino = $destino->getReferencias();
        $guiaTrackingMongo->numero_externo = $bitacoraCotizacionMensajeriaTO->getNumeroExterno(); //Es el numero de pedido de las tiendas
        $guiaTrackingMongo->codigo_postal_destino = $bitacoraCotizacionMensajeriaTO->getCodigoPostalDestino();
        $guiaTrackingMongo->envio_internacional = $bitacoraCotizacionMensajeriaTO->getEnvioInternacional();
        $guiaTrackingMongo->pais_destino = $bitacoraCotizacionMensajeriaTO->getPaisDestino();
        $guiaTrackingMongo->save();
    }

    public function buscaTraking(TrackingMensajeriaTO $trackingMensajeriaTO)
    {
        $trackingMensajeria = TrackingMensajeria::select(
            'num_orden',
            'id_seller',
            'id_mensajeria',
            'codigo',
            'guia',
            'descripcion',
            'familia_externa',
            'familia_interna',
            'notificaciones',
            'fecha_hora',
            'tracking_link',
            'id_estatus',
            'nombre_destino',
            'apellido_destino',
            'email_destino',
            'calle_destino',
            'numero_destino',
            'colonia_destino',
            'municipio_destino',
            'telefono_destino',
            'estado_destino',
            'referencia_destino',
            'numero_externo',
            'codigo_postal_destino',
            'g_ret',
            'nombre_recibe'
        );

        $trackingMensajeria->orderBy('fecha_hora', 'desc');

        $trackingMensajeria->when($trackingMensajeriaTO->getId(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('_id', $trackingMensajeriaTO->getId());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getIdSeller(), function ($query) use ($trackingMensajeriaTO) {
            //            die(print_r($trackingMensajeriaTO->getIdSeller()));
            $query->where('id_seller', $trackingMensajeriaTO->getIdSeller());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getGuia(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('guia', $trackingMensajeriaTO->getGuia());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getGuias(), function ($query) use ($trackingMensajeriaTO) {
            $query->whereIn('guia', $trackingMensajeriaTO->getGuias());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getFechaInicio(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('fecha_alta', '>=', $trackingMensajeriaTO->getFechaInicio()->startOfDay());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getFechaFin(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('fecha_alta', '<=', $trackingMensajeriaTO->getFechaFin()->endOfDay());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getIdMensajeria(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('id_mensajeria', $trackingMensajeriaTO->getIdMensajeria());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getNumOrden(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('num_orden', $trackingMensajeriaTO->getNumOrden());
        });

        $trackingMensajeria->when($trackingMensajeriaTO->getPedidcComercio(), function ($query) use ($trackingMensajeriaTO) {
            $query->where('numero_externo', $trackingMensajeriaTO->getPedidcComercio());
        });

        $trackingMensajeria->orderBy($trackingMensajeriaTO->getColumna(), $trackingMensajeriaTO->getOrder());


        //        die(print_r($trackingMensajeriaTO->getOrder()));
        if ($trackingMensajeriaTO->getFamiliaExterna()) {
            $guias = $trackingMensajeria->get();
            $guias =   $guias->whereIn('familia_externa', $trackingMensajeriaTO->getFamiliaExterna());
            $total = $guias->count();
            $guias = $guias
                ->slice($trackingMensajeriaTO->getLimit() * $trackingMensajeriaTO->getPage())
                ->take($trackingMensajeriaTO->getLimit());
        } else {

            $total = $trackingMensajeria->get()->count();

            $guias = $trackingMensajeria
                ->skip($trackingMensajeriaTO->getLimit() * $trackingMensajeriaTO->getPage())
                ->limit($trackingMensajeriaTO->getLimit())
                ->get();
        }
        // die(print_r($guias->toArray));
        //        Log::info($trackingMensajeria->get());


        return collect(['data' => $guias, 'total' => $total, 'filtrados' => $guias->count()]);
    }
}
