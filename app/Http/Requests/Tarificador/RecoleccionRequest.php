<?php

namespace App\Http\Requests\Tarificador;

use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;

class RecoleccionRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     *
     * @return bool
     */
    public function authorize()
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array
     */
    public function rules()
    {
        Log::info('----------Inicia validaciones Recoleccion----------------');
        Log::info($this->all());

        $middleware = $this->route()->middleware();
        $isPgs = in_array('jwt.auth',$middleware)?true:false;

        $rules = [
            'guia' => 'sometimes|required',
            'mensajeria' => 'in:DHL,FEDEX,REDPACK|required_if:guia,!=,', 
            'nombre_contacto' => 'required|min:3|max:50',
            'apellidos_contacto' => 'required|min:3|max:150',
            'email' => 'required|email',
            'calle' => 'required',
            'numero' => 'required',
            'colonia' => 'required|min:3',
            'telefono' => 'required|min:10',
            'estado' => 'required|min:4',
            'municipio' => 'required|min:3',
            'codigo_postal' => 'required|min:5|max:5',
            'referencias' => 'required',
            'cantidad_piezas' => 'required|integer',
            'peso' => 'required|integer',
            'largo' => 'required|integer',
            'ancho' => 'required|integer',
            'alto' => 'required|integer',
            'fecha' => 'required|date',
            'hora_inicio' => 'required|date_format:H:i:s',
            'horario_cierre' => 'required|date_format:H:i:s||after:hora_inicio'
        ];

        if($isPgs){
            $rules['comercio_id']  = 'required|exists:comercios,clave';
        }

        return $rules;
    }
}
