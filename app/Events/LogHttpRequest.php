<?php

namespace App\Events;

use Illuminate\Broadcasting\Channel;
use Illuminate\Http\Request;
use Illuminate\Queue\SerializesModels;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Broadcasting\PresenceChannel;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Support\Arr;

class LogHttpRequest
{
    use Dispatchable, InteractsWithSockets, SerializesModels;
    public $request;
    /**
     * Create a new event instance.
     *
     * @return void
     */
    public function __construct(Request $request, $json,$code)
    {
        $response = $json;
        if(Arr::exists($json,'detail')){
            if(Arr::exists($json['detail'],'file')){
                $response['detail']['file'] = '';
            }
        }

        $dataLog = [
            'request' => json_encode($request->all()),
            'response' => json_encode($response),
            'codigo_respuesta' => $code,
            'path'=>  $request->url(),
            'metodo'=> $request->getMethod()

        ];
//        die(print_r($dataLog));
        $this->request = $dataLog;
    }

    /**
     * Get the channels the event should broadcast on.
     *
     * @return \Illuminate\Broadcasting\Channel|array
     */
    public function broadcastOn()
    {
        return new PrivateChannel('channel-name');
    }
}
