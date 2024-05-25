<?php

namespace App\ClaroEnvios\Mensajerias\HistoricoEvento;


use App\ClaroEnvios\Comandos\ComandoRepositoryInterface;
use App\ClaroEnvios\Mensajerias\EventoGuiaMensajeria;
use App\ClaroEnvios\Mensajerias\GuiaMensajeriaTO;
use App\ClaroEnvios\Mensajerias\MensajeriaRepositoryInterface;
use Illuminate\Support\Facades\DB;

class MensajeriaHistoricoEventoRepository implements MensajeriaHistoricoEventoRepositoryInterface
{
    /**
     * @var MensajeriaRepositoryInterface
     */
    private $mensajeriaRepository;
    /**
     * @var ComandoRepositoryInterface
     */
    private $comandoRepository;

    /**
     * MensajeriaHistoricoEventoRepository constructor.
     */
    public function __construct(
        MensajeriaRepositoryInterface $mensajeriaRepository,
        ComandoRepositoryInterface $comandoRepository
    ) {
        $this->mensajeriaRepository = $mensajeriaRepository;
        $this->comandoRepository = $comandoRepository;
    }

    public function guardarHistoricoEventos(
        array $arrayGuiaMensajeriaTO,
        array $arrayEventosGuiasMensajerias,
        $comandoEjecucionTO = false
    ) {
        DB::transaction(
            function () use (
                $arrayGuiaMensajeriaTO,
                $arrayEventosGuiasMensajerias,
                $comandoEjecucionTO
            ) {
                foreach ($arrayGuiaMensajeriaTO as $guiaMensajeriaTO) {
                    if ($guiaMensajeriaTO instanceof GuiaMensajeriaTO) {
                        $this->mensajeriaRepository->modificarGuiaMensajeriaStatus($guiaMensajeriaTO);
                    }
                }
                EventoGuiaMensajeria::insert($arrayEventosGuiasMensajerias);
                if (is_object($comandoEjecucionTO)) {
                    $this->comandoRepository->guardarComandoEjecucion($comandoEjecucionTO);
                }
            }
        );
    }
}
