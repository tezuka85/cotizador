<?php

namespace App\Http\Requests\Tarificador;

use Illuminate\Foundation\Http\FormRequest;

class GuardarCotizarTarificadorRequest extends FormRequest
{
    public static $rules = [
        'codigo_postal_origen' => 'required|integer|exists:sepomex,d_codigo',
        'codigo_postal_destino' => 'required|integer|exists:sepomex,d_codigo',
        'tienda' => 'required|exists:tiendas,id',
        'peso' => 'required|integer',
        'largo' => 'required|integer',
        'ancho' => 'required|integer',
        'alto' => 'required|integer',
        'dias_embarque' => 'required|integer',
        'tipo_servicio'=> 'required|min:1'
    ];
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
        return self::$rules;
    }
}
