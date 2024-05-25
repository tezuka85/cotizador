<?php

namespace App\ClaroEnvios\Usuarios;


interface UsuarioRepositoryInterface
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

    public function registrarUsuario(UsuarioTO $usuarioTO);

    public function findUsuario(UsuarioTO $usuarioTO);

    public function guardarAccesoTokenUsuario(AccesoTokenUsuarioTO $accesoTokenUsuarioTO);
}
