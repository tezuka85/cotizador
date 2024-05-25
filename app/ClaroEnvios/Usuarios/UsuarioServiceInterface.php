<?php

namespace App\ClaroEnvios\Usuarios;


interface UsuarioServiceInterface
{

    /**
     * Metodo que guarda un Usuario nuevo
     * @param UsuarioTO $usuarioTO
     */
    public function guardarUsuario(UsuarioTO $usuarioTO);

    /**
     * Metodo que busca el usuario de acuerdo a los datos del TO
     * @param UsuarioTO $usuarioTO
     * @return mixed
     */
    public function buscarUsuario(UsuarioTO $usuarioTO);

    /**
     * Metodo que recupera el token de un usuario de acuerdo a las credenciales
     * @param UsuarioTO $usuarioTO
     * @throws \App\Exceptions\ValidacionException
     */
    public function loginUsuario(UsuarioTO $usuarioTO);

    public function registrarUsuario(UsuarioTO $usuarioTO);

    public function findUsuario(UsuarioTO $usuarioTO);
}
