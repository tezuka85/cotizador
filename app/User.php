<?php

namespace App;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\CuentaTipo\CuentaTipo;
use App\ClaroEnvios\Mensajerias\CostoMensajeria;
use App\ClaroEnvios\Usuarios\UsuarioComercio;
use Illuminate\Notifications\Notifiable;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Spatie\Permission\Traits\HasRoles;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, Notifiable;
    use HasRoles;

    protected $table = 'usuarios';

    protected $guard_name = 'web';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = ['nombres', 'apellidos', 'email', 'password','departamento_id'];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function comercio()
    {
        return $this->belongsTo(Comercio::class);
    }

    public function comercios()
    {
        return $this->hasMany(UsuarioComercio::class, 'usuario_id','id');
    }


    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function getCuentaTipo()
    {
        return $this->hasMany(CostoMensajeria::class, 'usuario_id');
    }

    /**
     * @return mixed
     * Obtiene el rol del usuario / Perfil
     */
    public function herRole(){
        return $this->roles->first();
    }
}
