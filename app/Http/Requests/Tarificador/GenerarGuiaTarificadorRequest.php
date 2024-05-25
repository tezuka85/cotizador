<?php

namespace App\Http\Requests\Tarificador;

use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class GenerarGuiaTarificadorRequest extends FormRequest
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
        $middleware = $this->route()->middleware();
        $isPgs = in_array('jwt.auth',$middleware)?true:false;

        $rules = [
            'nombre_comercion_origen' => 'max:70',
            'nombre_origen' => 'required|min:3|max:70',
            'apellidos_origen' => 'max:70',
            'email_origen' => 'required|email',
            'calle_origen' => 'required|min:3',
            'numero_origen' => 'required',
            'colonia_origen' => 'required|min:3',
            'telefono_origen' => 'required|min:8',
            'estado_origen' => 'required|min:3',
            'municipio_origen' => 'required|min:3',
            'referencias_origen' => 'required|min:3',
            'nombre_comercion_destino' => 'max:70',
            'nombre_destino' => 'required|min:3|max:70',
            'apellidos_destino' => 'max:70',
            'email_destino' => 'required|email',
            'calle_destino' => 'required|min:3',
            'numero_destino' => 'required',
            'colonia_destino' => 'required|min:3',
            'telefono_destino' => 'required|min:8',
            'estado_destino' => 'required|min:3',
            'municipio_destino' => 'required|min:3',
            'referencias_destino' => 'required|min:3',
            'generar_recoleccion' => 'boolean',
            'contenido' => 'required|max:50',
            'codigo_producto' => 'max:25',
            'codificacion' =>'in:utf8,base64',
            'tipo_documento' =>'in:pdf,zpl,zpl_6x4'
        ];

        if($isPgs){
            $rules['origen_guia'] = 'required|in:t1envios,t1paginas,t1comercios,shopify';
            $rules['tiene_notificacion'] = 'required|boolean';
        }

        return $rules;
    }
}
