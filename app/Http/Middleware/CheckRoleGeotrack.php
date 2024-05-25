<?php

namespace App\Http\Middleware;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Usuarios\UsuarioComercio;
use App\User;
use Closure;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Spatie\Permission\Exceptions\UnauthorizedException;

class CheckRoleGeotrack
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
        $token = json_decode(Auth::token());
        $userProvider = Auth::createUserProvider('users');
        $usuario =  $userProvider->retrieveByCredentials(['email' => $token->email]);
        Auth::setUser($usuario);

         //die(print_r(Auth::user()));
        if(property_exists($token,'groups') && property_exists($token,'id_tiendas')){
            $role = $token->resource_access->geotrack->roles[0];
          
            
            Auth::user()->assignRole($role);
          
            Log::info('------Peticion usuario: '. $token->email.'------');


            if (Auth::user()->hasRole('admin_sellers')) {
                // Establece la propiedad en el proveedor de autenticación
                // aquí, por ejemplo:
               
                Auth::user()->is_admin = true;
            }else{
                Auth::user()->is_admin = false;
            }

            if(in_array($role, $roles)){
              
                return $next($request);
            }
        }else{
            $request->usuario_id = Auth::user()->id;
        }
        
        return $next($request);
    }
}
