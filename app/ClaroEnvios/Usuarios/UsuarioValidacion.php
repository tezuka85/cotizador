<?php

namespace App\ClaroEnvios\Usuarios;


use App\Exceptions\ValidacionException;
use Illuminate\Support\Facades\Hash;

class UsuarioValidacion
{
    /**
     * @var UsuarioRepositoryInterface
     */
    private $usuarioRepository;

    /**
     * UsuarioValidacion constructor.
     */
    public function __construct(
        UsuarioRepositoryInterface $usuarioRepository
    ) {
        $this->usuarioRepository = $usuarioRepository;
    }

    public function validarCredenciales(UsuarioTO $usuarioTO)
    {
        $usuario = $this->usuarioRepository->buscarUsuario($usuarioTO);
        if (!Hash::check($usuarioTO->getPassword(), $usuario->password)) {
            throw new ValidacionException('Password incorrecto!');
        }
    }
}
