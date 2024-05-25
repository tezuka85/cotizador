<?php

namespace App\Http\Middleware;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Usuarios\UsuarioComercio;
use App\User;
use Closure;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Illuminate\Auth\AuthenticationException;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next,  ...$roles)
    {
        if(empty($_SERVER['HTTP_AUTHORIZATION'])) {
            throw new AuthenticationException('authorization header not found');
        }

        // check if bearer token exists
        if (! preg_match('/Bearer\s(\S+)/', $_SERVER['HTTP_AUTHORIZATION'], $matches)) {
            throw new AuthenticationException('token not found');
        }

        // extract token
        $jwt = $matches[1];
        if (!$jwt) {
            throw new AuthenticationException('could not extract token');
        }

       $key = "-----BEGIN PUBLIC KEY-----\n".env('KEYCLOAK_REALM_PUBLIC_KEY')."\n-----END PUBLIC KEY-----\n";
       JWT::$leeway = 120; // $leeway in seconds
       $token = JWT::decode( $jwt, new Key($key, 'RS256'));
   
        $type = '';

        if(property_exists($token,'clientId')){
            $type = 'app';
        }elseif (property_exists($token,'email')){
            $type = 'user';
        }

        $userProvider = Auth::createUserProvider('users');

        if($type == 'user'){
            $usuario =  $userProvider->retrieveByCredentials(['email' => $token->email]);
     
            Log::info('------Peticion usuario: '. $token->email.'------');
            Auth::setUser($usuario);

            if($usuario instanceof User){
                if($request->comercio_id) {
                
                    $comercio = Comercio::withTrashed()->where('clave', $request->comercio_id)->first();
                    $middleware = $request->route()->uri;
                    
                    if (!$comercio) {
                        $mensaje = "No existe comercio con id: {$request->comercio_id}.";
                        Log::info($mensaje);
                        throw new UnauthorizedException(404, $mensaje);
                    } elseif (($comercio->is_deleted == 1) && ($middleware != 't1/pgs/comercios/verificar' && $middleware != 't1/pgs/mensajeria-cotizador')) {

                        $mensaje = "Comercio con id: {$request->comercio_id}. desactivado";
                        Log::info($mensaje);
                        throw new UnauthorizedException(404, $mensaje);
                    } elseif ($comercio->is_deleted == 2 && ($middleware != 't1/pgs/comercios/verificar' && $middleware != 't1/pgs/mensajeria-cotizador')) {
                        $mensaje = "El comercio se encuentra en revisión";
                        Log::info($mensaje);
                        throw new UnauthorizedException(404, $mensaje);
                    }

                    $userCommerce = UsuarioComercio::where('usuario_id', $usuario->id)
                        ->where('comercio_id', $comercio->id)
                        ->first();

                    if (!$userCommerce) {
                        throw new UnauthorizedException(401,
                            "El usuario no tiene asignado el comercio : {$request->comercio_id}.");
                    }

                    $rolesUser = $userCommerce->getRoleNames();
                    $checkRoles = $rolesUser->intersect($roles);

                    foreach ($checkRoles as $role) {
                        if (!$userCommerce->hasRole($role)) {
                            throw new UnauthorizedException(401,
                                "El usuario no tiene permisos necesarios para realizar esta acción");
                        }
                    }
                }else{
                    throw new UnauthorizedException(401,
                        "Faltan parametros para esta peticion");
                }

            }else{
                $mensaje = "No se encontro usuario: {$token->email}.";
                Log::info($mensaje);
                throw new UnauthorizedException(404,$mensaje);
            }

        }elseif ($type == 'app'){
            $usuario =  $userProvider->retrieveByCredentials(['nombres' => $token->preferred_username]);
            Log::info('------Peticion app: '. $token->preferred_username.'------');
            if(!$usuario){
                throw new UnauthorizedException(404,"No se encontro usuario: {$token->preferred_username}.");
            }

            Auth::setUser($usuario);
            $allowedClients = in_array($token->clientId,['t1envios','identity-API']);

            if ($allowedClients == false) {
                throw new UnauthorizedException(401,"La aplicacion no tiene permisos necesarios para realizar esta acción");
            }
        }else{
            throw new UnauthorizedException(404,"Recurso desconocido");
        }

        return $next($request);
    }
}
