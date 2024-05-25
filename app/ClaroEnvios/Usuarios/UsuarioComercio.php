<?php

namespace App\ClaroEnvios\Usuarios;


use App\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Spatie\Permission\Traits\HasRoles;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class UsuarioComercio extends Model
{
    use SoftDeletesBoolean;
    use HasRoles;

    protected $table = 'usuarios_comercios';
    protected $fillable = ['id', 'usuario_id','comercio_id'];
    protected $dates = ['created_at','updated_at'];
    protected $guard_name = 'web';

    public function comercios()
    {
        return $this->hasMany(Comercio::class, 'comercio_id', 'id');
    }

    public function usuarios()
    {
        return $this->hasMany(User::class, 'usuario_id', 'id');
    }

    public function herRole(){
        return $this->roles->first();
    }

}
