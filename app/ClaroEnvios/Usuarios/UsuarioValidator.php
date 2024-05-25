<?php

namespace App\ClaroEnvios\Usuarios;


/**
 * Class UsuarioValidator
 * @package App\ClaroEnvios\Usuarios
 */
class UsuarioValidator implements UsuarioServiceInterface
{
    /**
     * @var UsuarioService
     */
    private $usuarioService;
    /**
     * @var UsuarioValidacion
     */
    private $usuarioValidacion;

    /**
     * UsuarioValidator constructor.
     */
    public function __construct(
        UsuarioService $usuarioService,
        UsuarioValidacion $usuarioValidacion
    ) {
        $this->usuarioService = $usuarioService;
        $this->usuarioValidacion = $usuarioValidacion;
    }

    /**
     * Metodo que guarda un Usuario nuevo
     * @param UsuarioTO $usuarioTO
     */
    public function guardarUsuario(UsuarioTO $usuarioTO)
    {
        $this->usuarioService->guardarUsuario($usuarioTO);
    }

    /**
     * Metodo que busca el usuario de acuerdo a los datos del TO
     * @param UsuarioTO $usuarioTO
     * @return mixed
     */
    public function buscarUsuario(UsuarioTO $usuarioTO)
    {
        return $this->usuarioService->buscarUsuario($usuarioTO);
    }

    /**
     * Metodo que recupera el token de un usuario de acuerdo a las credenciales
     * @param UsuarioTO $usuarioTO
     * @throws \App\Exceptions\ValidacionException
     */
    public function loginUsuario(UsuarioTO $usuarioTO)
    {
        $this->usuarioValidacion->validarCredenciales($usuarioTO);
        $this->usuarioService->loginUsuario($usuarioTO);
    }

    public function registrarUsuario(UsuarioTO $usuarioTO)
    {
        $this->usuarioService->registrarUsuario($usuarioTO);
    }

    public function findUsuario(UsuarioTO $usuarioTO)
    {
        return $this->usuarioService->findUsuario($usuarioTO);
    }
}
