<?php

namespace App\ClaroEnvios\Usuarios;


use App\ClaroEnvios\Comercios\ComercioRepositoryInterface;
use App\ClaroEnvios\Comercios\ComercioTO;
use App\User;
use Illuminate\Support\Facades\DB;

/**
 * Class UsuarioRepository
 * @package App\ClaroEnvios\Usuarios
 */
class UsuarioRepository implements UsuarioRepositoryInterface
{
    /**
     * @var ComercioRepositoryInterface
     */
    private $comercioRepository;

    /**
     * UsuarioRepository constructor.
     */
    public function __construct(ComercioRepositoryInterface $comercioRepository)
    {
        $this->comercioRepository = $comercioRepository;
    }

    /**
     * Metodo que guarda un Usuario nuevo
     * @param UsuarioTO $usuarioTO
     */
    public function guardarUsuario(UsuarioTO $usuarioTO)
    {
        $usuario = new User();
        $usuario->nombres = $usuarioTO->getNombres();
        $usuario->apellidos = $usuarioTO->getApellidos();
        $usuario->email = $usuarioTO->getEmail();
        $usuario->departamento_id = $usuarioTO->getDepartamentoId();
        $usuario->comercio_id = $usuarioTO->getComercioId();
        $usuario->password = $usuarioTO->getPassword();
        $usuario->save();
        $usuarioTO->setId($usuario->id);
        $usuario->assignRole($usuarioTO->getRole());
    }

    /**
     * Metodo que busca el usuario de acuerdo a los datos del TO
     * @param UsuarioTO $usuarioTO
     * @return mixed
     */
    public function buscarUsuario(UsuarioTO $usuarioTO)
    {
        $usuario = User::query();
        $usuario->when(
            $usuarioTO->getEmail(),
            function ($query) use ($usuarioTO) {
                $query->where('email', '=', $usuarioTO->getEmail());
            }
        );
        if ($usuarioTO->getFirst()) {
            return $usuario->first();
        }
        return $usuario->get();
    }

    /**
     * @param UsuarioTO $usuarioTO
     */
    public function registrarUsuario(UsuarioTO $usuarioTO)
    {
        DB::transaction(
            function () use($usuarioTO) {
                $comercioTO = $usuarioTO->getComercioTO();
                if ($comercioTO instanceof ComercioTO) {
                    $this->comercioRepository->guardarComercio($comercioTO);
                    $usuarioTO->setComercioId($comercioTO->getId());
                }
                $this->guardarUsuario($usuarioTO);
                $accesoTokenUsuarioTO = new AccesoTokenUsuarioTO();
                $accesoTokenUsuarioTO->setUsuarioId($usuarioTO->getId());
            }
        );
    }

    /**
     * @param UsuarioTO $usuarioTO
     * @return mixed
     */
    public function findUsuario(UsuarioTO $usuarioTO)
    {
        return User::find($usuarioTO->getId());
    }

    /**
     * @param AccesoTokenUsuarioTO $accesoTokenUsuarioTO
     */
    public function guardarAccesoTokenUsuario(
        AccesoTokenUsuarioTO $accesoTokenUsuarioTO
    ) {
        AccesoTokenUsuario::create($accesoTokenUsuarioTO->toArray());
    }
}
