<?php

namespace App\ClaroEnvios\BitacoraRequest;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Webkid\LaravelBooleanSoftdeletes\SoftDeletesBoolean;

class BitacoraRequest extends Model
{
    use SoftDeletesBoolean;

    protected $table = 'bitacora_requests';
    protected $fillable = ['usuario_id', 'metodo','codigo_respuesta','request','response','path','created_at'];
    protected $dates = ['created_at'];
    const UPDATED_AT = null;



    public function usuario()
    {
        return $this->hasOne(User::class,'usuario_id');
    }

}