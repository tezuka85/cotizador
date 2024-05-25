<?php

namespace App\ClaroEnvios\Usuarios;


use App\Exceptions\ValidacionException;
use Carbon\Carbon;
use Laravel\Passport\Passport;

class UsuarioService implements UsuarioServiceInterface
{
    /**
     * @var UsuarioRepositoryInterface
     */
    private $usuarioRepository;

    const TIEMPO_EXPIRA_CLARO = 48;
    const TIEMPO_EXPIRA_COMERCIO = 60;

    /**
     * UsuarioService constructor.
     */
    public function __construct(UsuarioRepositoryInterface $usuarioRepository)
    {
        $this->usuarioRepository = $usuarioRepository;
    }

    /**
     * Metodo que guarda un Usuario nuevo
     * @param UsuarioTO $usuarioTO
     */
    public function guardarUsuario(UsuarioTO $usuarioTO)
    {
        $this->usuarioRepository->guardarUsuario($usuarioTO);
    }

    /**
     * Metodo que busca el usuario de acuerdo a los datos del TO
     * @param UsuarioTO $usuarioTO
     * @return mixed
     */
    public function buscarUsuario(UsuarioTO $usuarioTO)
    {
        return $this->usuarioRepository->buscarUsuario($usuarioTO);
    }

    /**
     * Metodo que recupera el token de un usuario de acuerdo a las credenciales
     * @param UsuarioTO $usuarioTO
     * @throws \App\Exceptions\ValidacionException
     */
    public function loginUsuario(UsuarioTO $usuarioTO)
    {
        $usuario = $this->usuarioRepository->buscarUsuario($usuarioTO);
        Passport::routes();

        Passport::personalAccessTokensExpireIn(Carbon::now()->addHours(self::TIEMPO_EXPIRA_CLARO));
        Passport::tokensExpireIn(now()->addHours(self::TIEMPO_EXPIRA_CLARO));

        // Passport::personalAccessTokensExpireIn(Carbon::now()->addYears(100));
        // Passport::tokensExpireIn(now()->addYears(100));
        //die("<pre>".var_dump($usuario->hasRole('superadministrador')));
        $scope = ($usuario->hasRole('superadministrador') || $usuario->hasRole('supervisor'))?['admin']:['comercio'];
        $usuarioTO->setToken($usuario->createToken('accessToken',$scope)->accessToken);

            $accesoTokenUsuarioTO = new AccesoTokenUsuarioTO();
            $accesoTokenUsuarioTO->setUsuarioId($usuario->id);
            $accesoTokenUsuarioTO->setToken($usuarioTO->getToken());
            $this->usuarioRepository->guardarAccesoTokenUsuario($accesoTokenUsuarioTO);

    }

    public function registrarUsuario(UsuarioTO $usuarioTO)
    {
        $this->usuarioRepository->registrarUsuario($usuarioTO);
    }

    public function findUsuario(UsuarioTO $usuarioTO)
    {
        return $this->usuarioRepository->findUsuario($usuarioTO);
    }
}
