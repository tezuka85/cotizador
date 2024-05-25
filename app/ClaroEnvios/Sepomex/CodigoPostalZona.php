<?php
/**
 * Created by PhpStorm.
 * User: dev01
 * Date: 25/05/18
 * Time: 06:58 PM
 */

namespace App\ClaroEnvios\Sepomex;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Class Sepomex
 * @package App\Models
 */
class CodigoPostalZona extends Model
{
    /**
     * @var bool
     */
    public $timestamps = false;
    /**
     * @var string
     */
    protected $table = 'codigos_postales_zonas';



}
