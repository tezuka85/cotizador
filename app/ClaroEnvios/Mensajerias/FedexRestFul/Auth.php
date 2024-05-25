<?php

namespace App\ClaroEnvios\Mensajerias\FedexRestFul;

use App\ClaroEnvios\Mensajerias\Accesos\AccesoComercioMensajeria;
use GuzzleHttp\Client;


class Auth
{
    private $clientId;
    private $secretId;
    private $token;
    private $tokenExpiredAt;

    public function __construct($clientId, $secretId, $token, $tokenExpiredAt)
    {
        $this->clientId = $clientId;
        $this->secretId = $secretId;
        $this->token = $token;
        $this->tokenExpiredAt = $tokenExpiredAt;
    }

    public function getToken()
    {
        if (!$this->token || $this->isTokenExpired($this->tokenExpiredAt)) {
        
            // Si no hay token o el token ha expirado, obtener uno nuevo
            $client = new Client();
            $baseUrl = env('FEDEX_URL');
            $response = $client->post($baseUrl.'oauth/token', [
                'form_params' => [
                    'grant_type' => 'client_credentials',
                    'client_id' => $this->clientId,
                    'client_secret' => $this->secretId,
                ],
            ]);

            // Procesar la respuesta y obtener el token
            
            $body = json_decode($response->getBody());
            $this->token = $body->access_token;
            $this->tokenExpiredAt = $body->expires_in;

            // Guardar el token y la fecha de expiración
          /*  AccesoComercioMensajeria::join('accesos_campos_mensajerias', 'accesos_campos_mensajerias.id', '=', 'accesos_campos_mensajerias.id')
                ->where('accesos_campos_mensajerias.clave', 'token')
                ->update(['valor' => $this->token,'accesos_comercios_mensajerias.updated_at'=>now()]);

            AccesoComercioMensajeria::join('accesos_campos_mensajerias', 'accesos_campos_mensajerias.id', '=', 'accesos_campos_mensajerias.id')
                ->where('accesos_campos_mensajerias.clave', 'token_expires_at')
                ->update(['valor' => now()->addSeconds($this->tokenExpiredAt/1000)]);
*/

        }

        return $this->token;
    }

    private function isTokenExpired($tokenExpiresAt)
    {
        return !$tokenExpiresAt || now()->gte($tokenExpiresAt);
    }
}
