<?php
namespace App\ClaroEnvios\T1Paginas;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
use Illuminate\Support\Facades\Log;

class ConsumeAPIIdentity
{

    private $url_api;
    private $url_sso;
    private $client_id;
    private $client_secret;


    /**
     * ComercioController constructor.
     */
    public function __construct() {
        $this->client_id = env('IDENTITY_CLIENT_ID');
        $this->client_secret = env('IDENTITY_CLIENT_SECRET');
        $this->url_api = env('IDENTITY_HOST');
        $this->url_sso = env('SSO_HOST');

    }


    public function getToken()
    {
        Log::info('getToken SSO');
        Log::info('url SSO: '.$this->url_sso.'protocol/openid-connect/token');
        Log::info('client_id: '.$this->client_id);
        Log::info('client_secret: '.$this->client_secret);

        try{

            $options = [
                'headers' => ['Content-Type' => 'application/x-www-form-urlencoded']
            ];

            $client = new Client($options);

            $response = $client->post($this->url_sso.'protocol/openid-connect/token', [
                'form_params' =>  [
                    "grant_type" => "client_credentials",
                    "client_id" => $this->client_id,
                    "client_secret" => $this->client_secret
                ]
            ])->getBody()->getContents();


            $object = json_decode($response);
//            die("<pre>".print_r( $object->access_token, true));
            Log::info($object->access_token);

            return $object->access_token;

        }catch(\Exception $exception){
            error_log($exception->getMessage().' '.$exception->getFile().': '.$exception->getLine());
            echo $exception->getMessage().' '.$exception->getFile().': '.$exception->getLine();
        }

    }


    public function getComercio($id){
         $response = $this->makeRequest('identity/v1/store/'.$id);

        return $response;
    }

    public function getUsuariosComercio($comercioId){
        $response = $this->makeRequest("identity/v1/store/$comercioId/user/");

        return $response;
    }

    public function getUsuario($username){
        $response = $this->makeRequest("identity/v1/user/$username");

        return $response;
    }

    public function getTaxComercio($comercioId){
        $response = $this->makeRequest("identity/v1/store/$comercioId/tax/");

        return $response;
    }


    protected function makeRequest($type){

        Log::info('makeRequestT1Paginas');
        Log::info($this->url_api.$type);
        try{
            $headers = [
                'headers' => ['Accept' => 'application/json', 'Content-Type'=>'application/json','Authorization'=>'Bearer '.$this->getToken()],
//                'defaults' => ['verify' => false]
            ];

            $client = new Client($headers);
            $response = $client->request('GET',$this->url_api.$type)->getBody()->getContents();
            $responseObject = json_decode($response);

            Log::info($response);


            return $responseObject;

        }catch (ClientException $exception){
            Log::error($exception->getMessage());
            $responseBody = $exception->getResponse()->getBody(true);
            $response = json_decode($responseBody->getContents());
//            die(print_r($response));
            throw new \Exception($exception->getMessage());

        }


    }
}
