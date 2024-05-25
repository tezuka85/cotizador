<?php

namespace App\ClaroEnvios\Mensajerias\FedexRestFul;

use App\ClaroEnvios\Mensajerias\FedexRestFul\Auth;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\RequestOptions;
use Illuminate\Support\Facades\Log;

class FedexRestFul
{
    private $client;
    protected $baseUrl;
    protected $data;
    protected $endpoint;

    public function __construct()
    {
    }


    public function makeRequest()
    {
        // Realiza la solicitud a la API de FedEx usando GuzzleHttp
        try {

            $this->baseUrl = env('FEDEX_URL');
            $clientId = env('FEDEX_CLIENT_ID');
            $clientSecret = env('FEDEX_CLIENT_SECRET');
            //$auth = new Auth('l7c767b6c71d424d6686459ea81f89c43f','c98e980816824ce0960384679d875e54','','');
            $auth = new Auth($clientId, $clientSecret, '', '');
            $token = $auth->getToken();
            Log::info('URL Recoleccion FEDEX: '. $this->baseUrl .$this->endpoint);

            // $headers = [
            //     'headers' => ['Accept' => 'application/json', 'Content-Type'=>'application/json','Authorization'=>'Bearer '.$token],
            // ];

            // $client = new Client($headers);
            // $response = $client->request('POST',$this->baseUrl.$this->endpoint, [RequestOptions::JSON=>$this->data]);
            // Configuración de la solicitud
            $headers = [
                'X-locale' => 'en_US',
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $token,
            ];
            // Datos de la solicitud

            $data = $this->data;
            $client = new Client();
            // Realizar la solicitud POST
            $response = $client->post($this->baseUrl . $this->endpoint, [
                'headers' => $headers,
                'json' => $data,
            ]);

            // Obtener el cuerpo de la respuesta
            // $body = $response->getBody()->getContents();

            return ($response);
        } catch (RequestException $exception) {

            Log::error('error RequestException makeRequest: ' . $exception->getMessage());
            $response = $exception->getResponse();

            $responseBody = $response->getBody()->getContents();

            Log::error('error Fedex: ' . $responseBody);

            return [
                'error' => json_decode($responseBody),
            ];
        } catch (\Exception $exception) {
            Log::error('error Exception makeRequest: ' . $exception->getMessage());
            return [
                'error' => $exception->getMessage(),
            ];
        }
    }
}
