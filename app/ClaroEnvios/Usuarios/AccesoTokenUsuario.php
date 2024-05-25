<?php

namespace App\ClaroEnvios\Usuarios;


use Illuminate\Database\Eloquent\Model;

class AccesoTokenUsuario extends Model
{
    protected $table = 'accesos_tokens_usuarios';

    protected $fillable = ['usuario_id', 'token'];
}