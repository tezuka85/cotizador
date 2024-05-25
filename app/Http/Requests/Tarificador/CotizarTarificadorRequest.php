<?php

namespace App\Http\Requests\Tarificador;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class CotizarTarificadorRequest extends FormRequest
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

        $rules = [
            'envio_internacional' => 'boolean',
            'pais_destino' => 'required_if:envio_internacional,true|not_in:MX',
            'codigo_postal_origen' => 'required|digits:5',
            'codigo_postal_destino' => 'required',
            'dias_embarque' => 'required|integer',
            'seguro' => 'required|boolean',
            'valor_paquete' => 'required_if:seguro,1',
            'tipo_paquete' => 'required|integer|between:1,2',
            'pedido_comercio' => 'max:50',
            'moneda'  => 'required_if:envio_internacional,true|in:USD',
            'paquetes' => 'numeric|min:1',
            'paquetes_detalle' => ['array', 'size:' . request()->input('paquetes')]
        ];

        if($this->tipo_paquete == 2){
            $rules['peso']  = 'required|integer|max:101|min:1';
            $rules['largo'] = 'required|integer|min:1';
            $rules['ancho'] = 'required|integer|min:1';
            $rules['alto'] ='required|integer|min:1';
        }

//        die(var_dump($this->envio_internacional));
        if($this->envio_internacional == false){
            $rules['codigo_postal_destino'] = 'digits:5';
        }


        $middleware = $this->route() ? $this->route()->middleware():[];
        $isPgs = in_array('jwt.auth',$middleware)?true:false;
        $rules2 = [];

        if($isPgs){
            $rules['comercio_id']  = 'required|exists:comercios,clave';
           
            $rules2 = [
                'productos' => 'sometimes|array',
                'productos.*' => 'required|array',
                'productos.*.descripcion_sat' => 'required|max:150',
                'productos.*.codigo_sat' => 'required',
                'productos.*.peso' => 'required|integer',
                'productos.*.largo' => 'required|integer',
                'productos.*.ancho' => 'required|integer',
                'productos.*.alto' => 'required|integer',
                'productos.*.precio' => [
                    'required',
                    'regex:/^\d+(\.\d{1,2})?$/'
                ]
            ];
        }

        $rules = array_merge($rules,$rules2);


//        die(print_r($middleware));
        return $rules;
    }

    public function messages()
    {
        return [
            'valor_paquete.required_if' => 'El costo del paquete es requerido'
        ];
    }
}
