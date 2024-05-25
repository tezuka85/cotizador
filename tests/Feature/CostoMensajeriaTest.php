<?php

namespace Tests\Feature;

use App\ClaroEnvios\CuentaTipo\CuentaTipo;
use App\ClaroEnvios\Mensajerias\Mensajeria;
use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CostoMensajeriaTest extends TestCase
{
    private $headers;
    protected function setUp()
    {
        parent::setUp();
        $usuario = User::first();
        $token = $usuario->createToken('AccessToken')->accessToken;
        $this->headers = [
            'Accept'=> 'application/json',
            'Content-Type' => 'Content-Type',
            'Authorization' => 'Bearer '.$token
        ];
    }
    /**
     * Guardado de porcentajes por mensajeria
     *
     * @return void
     */
    public function testStoreSinAsignacionMultiple()
    {
        $usuario = User::whereNotNull('comercio_id')->first();
        $cuentaTipo = CuentaTipo::whereHas(
            'negociaciones',
            function ($query) {
                $query->where('asignacion_multiple', '=', 0);
            }
        )->first();
        $negociacion = $cuentaTipo->negociaciones->filter(
            function ($elemento) {
                return $elemento->asignacion_multiple == 0;
            }
        )->first();
        $mensajerias = Mensajeria::all();
        $arrayMensajeriasPorcentajes = $mensajerias->mapWithKeys(
            function ($mensajeria) {
                return [$mensajeria->id => [
                        'porcentaje' => (($mensajeria->id*10)/2),
                        'costo' => (($mensajeria->id*10)/2) + 2,
                        'porcentaje_seguro' => (($mensajeria->id*10)/5)
                    ]
                ];
            }
        )->toArray();
        $data = [
            'usuario_id' => $usuario->id,
            'cuenta_tipo_id' => $cuentaTipo->id,
            'negociacion_id' => $negociacion->id,
            'mensajerias_porcentajes' => $arrayMensajeriasPorcentajes
        ];
        $url = route('mensajerias.costos-mensajerias.store');
         $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertStatus(200)
            ->assertJson(["status"=>"ok","message"=>"Registro de porcentajes exitoso!"]);
    }

    /**
     * Guardado general de porcentajes
     *
     * @return void
     */
    public function testStoreAsignacionMultiple()
    {
        $usuario = User::whereNotNull('comercio_id')->first();
        $cuentaTipo = CuentaTipo::whereHas(
            'negociaciones',
            function ($query) {
                $query->where('asignacion_multiple', '=', 1);
            }
        )->first();
        $negociacion = $cuentaTipo->negociaciones->filter(
            function ($elemento) {
                return $elemento->asignacion_multiple == 1;
            }
        )->first();
        $data = [
            'usuario_id' => $usuario->id,
            'cuenta_tipo_id' => $cuentaTipo->id,
            'negociacion_id' => $negociacion->id,
            'porcentaje' => 13,
            'costo' => 15.67,
            'porcentaje_seguro' => .95
        ];
        $url = route('mensajerias.costos-mensajerias.store');
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertStatus(200)
            ->assertJson(["status"=>"ok","message"=>"Registro de porcentajes exitoso!"]);
    }
}
