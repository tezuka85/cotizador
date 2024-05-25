<?php

namespace App\Console\Commands\Mensajerias;

use App\ClaroEnvios\Comandos\ComandoEjecucionTO;
use App\ClaroEnvios\Comandos\ComandoServiceInterface;
use App\ClaroEnvios\Mensajerias\GuiaMensajeriaTO;
use App\ClaroEnvios\Mensajerias\HistoricoEvento\MensajeriaHistoricoEventoServiceInterface;
use App\ClaroEnvios\Mensajerias\MensajeriaCotizable;
use App\ClaroEnvios\Mensajerias\MensajeriaServiceInterface;
use App\ClaroEnvios\Mensajerias\MensajeriaTO;
use App\User;
use Illuminate\Console\Command;

class MensajeriasHistoricoEventos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mensajeria:historicoEventos';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Busca el historico de los eventos en el WebService';
    /**
     * @var MensajeriaServiceInterface
     */
    private $mensajeriaService;
    /**
     * @var MensajeriaHistoricoEventoServiceInterface
     */
    private $mensajeriaHistoricoEventoService;
    /**
     * @var ComandoServiceInterface
     */
    private $comandoService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(
        MensajeriaServiceInterface $mensajeriaService,
        MensajeriaHistoricoEventoServiceInterface $mensajeriaHistoricoEventoService,
        ComandoServiceInterface $comandoService
    ) {
        parent::__construct();
        $this->mensajeriaService = $mensajeriaService;
        $this->mensajeriaHistoricoEventoService = $mensajeriaHistoricoEventoService;
        $this->comandoService = $comandoService;
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $comandoEjecucionTO = new ComandoEjecucionTO(
            [
                'fecha_inicio'=>date('Y-m-d H:i:s'),
                'clase'=>get_class()
            ]
        );
        $this->comandoService->parametrosComandoEjecucion($comandoEjecucionTO);
        $guiaMensajeriaTO = new GuiaMensajeriaTO();
        $guiaMensajeriaTO->setStatusEntrega(1);
        $guiasMensajeriasTotales = $this->mensajeriaService
            ->buscarGuiaMensajeria($guiaMensajeriaTO);
        $guiasMensajeriasTotales->load('bitacoraCotizacionMensajeria','eventosGuiasMensajerias');
        $arrayGuiasUsuarios = $guiasMensajeriasTotales->groupBy('usuario_id');

        $arrayEventosGuiasMensajerias = [];
        $arrayGuiaMensajeriaTO = [];
        foreach ($arrayGuiasUsuarios as $usuario_id=>$guiasMensajerias) {
            $usuario = User::find($usuario_id);
            $guiasMensajerias = $guiasMensajerias->groupBy('bitacoraCotizacionMensajeria.mensajeria_id');
            $arrayMensajeriasId = $guiasMensajerias->keys()->toArray();
            $mensajerias = $this->mensajeriaService->buscarMensajeriasByIds($arrayMensajeriasId);
            foreach ($mensajerias as $mensajeria) {
                $mensajeriaTO = new MensajeriaTO();
                $mensajeriaTO->setId($mensajeria->id);
                $mensajeriaTO->setComercio($usuario->comercio_id);
                $mensajeriaEmpresa = new $mensajeria->clase();
                if ($mensajeriaEmpresa instanceof MensajeriaCotizable) {
                    $guiasMensajeriaEmpresa = $guiasMensajerias->get($mensajeria->id)
                        ->keyBy('guia');
                    $guiasArray  = $guiasMensajeriaEmpresa->pluck('guia')->toArray();
                    $arrayRastreo = $mensajeriaEmpresa->buscarGuiasArray($guiasArray);
                    foreach ($arrayRastreo as $rastreo) {
                        $guiaMensajeria = $guiasMensajeriaEmpresa->get($rastreo->guia);
                        $guiaMensajeriaTO = new GuiaMensajeriaTO();
                        $guiaMensajeriaTO->setId($guiaMensajeria->id);
                        $guiaMensajeriaTO->setStatusEntrega($rastreo->status_entrega);
                        $eventoGuiaMensajeria = $guiaMensajeria->eventosGuiasMensajerias
                            ->map(
                                function ($elemento) {
                                    return $elemento->codigo.'_'.$elemento->ubicacion.'_'.$elemento->fecha;
                                }
                            )->toArray();
                        if ($rastreo->status_entrega == 10) {
                            $guiaMensajeriaTO->setFechaStatusEntrega($rastreo->fecha_entrega);
                            $arrayGuiaMensajeriaTO[] = $guiaMensajeriaTO;
                        }
                        foreach ($rastreo->eventos as $evento) {
                            $key = $evento->codigo_evento.'_'.$evento->ubicacion.'_'.$evento->fecha_entrega;
                            if (!in_array($key, $eventoGuiaMensajeria)) {
                                $arrayEventosGuiasMensajerias[] = [
                                    'guia_mensajeria_id' => $guiaMensajeria->id,
                                    'codigo' => $evento->codigo_evento,
                                    'evento' => $evento->evento,
                                    'ubicacion' => $evento->ubicacion,
                                    'fecha' => $evento->fecha_entrega,
                                    'created_at' => date('Y-m-d H:i:s'),
                                    'updated_at' => date('Y-m-d H:i:s')
                                ];
                            }
                        }
                    }
                }
            }
        }

        $this->mensajeriaHistoricoEventoService
            ->guardarHistoricoEventos(
                $arrayGuiaMensajeriaTO,
                $arrayEventosGuiasMensajerias,
                $comandoEjecucionTO
            );
        $this->info('Comando ejecutado correctamente');
    }
}
