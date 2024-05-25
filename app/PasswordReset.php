<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PasswordReset extends Model
{
    protected $primaryKey = 'email';
    public $incrementing = false;

    protected $fillable = [
        'email', 'token'
    ];

    const UPDATED_AT = null;
}
