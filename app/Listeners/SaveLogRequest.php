<?php

namespace App\Listeners;

use App\ClaroEnvios\BitacoraRequest\BitacoraRequest;
use App\Events\LogHttpRequest;
use Carbon\Carbon;
use contadores_pot_sears\model\BitacoraContadoresPotModel;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class SaveLogRequest
{
    /**
     * Create the event listener.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * @param LogHttpRequest $event
     * @throws \Exception
     */
    public function handle(LogHttpRequest $event)
    {
        try{
            Log::info("Inicia Guardar log");
            $data = $event->request;
            $date = Carbon::now();
            $userId = Auth::user()?Auth::user()->id:null;
            if($userId){
                Log::info("Ruta: ".$data['path']);
                $bitacoraRequest = new BitacoraRequest();
                $bitacoraRequest->usuario_id = $userId;
                $bitacoraRequest->metodo = $data['metodo'];
                $bitacoraRequest->codigo_respuesta = $data['codigo_respuesta'];
                $bitacoraRequest->request = $data['request'];
                $bitacoraRequest->response = $data['response'];
                $bitacoraRequest->path = $data['path'];
                $bitacoraRequest->created_at = $date->format('Y-m-d H:i:s');
//        die(print_r($bitacoraRequest->toArray()));
                $bitacoraRequest->save();
                Log::info("Termina Guardar log");
            }
        }catch (\Exception $exception){
            Log::error($exception->getMessage());
            throw new \Exception("Error al guardar log");
        }

    }
}
