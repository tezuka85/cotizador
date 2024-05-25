<?php

namespace Tests\Feature;

use App\ClaroEnvios\Comercios\Comercio;
use App\ClaroEnvios\Comercios\Tipos\DireccionTipo;
use App\ClaroEnvios\Departamentos\Departamento;
use App\ClaroEnvios\Productos\ProductoTipo;
use App\User;
use Faker\Generator;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class UsuarioTest
 * @package Tests\Feature
 */
class UsuarioTest extends TestCase
{
    /**
     * @var
     */
    private $headers;

    /**
     * setUp
     */
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
     * Registro de Usuario
     *
     * @return void
     */
    public function testRegister()
    {
        $departamento = Departamento::first();
        $productoTipo = ProductoTipo::first();
        $comercio = Comercio::first();
        $data = factory(User::class)->make()->toArray();
        $data['password'] = 'secret';
        $data['departamento_id'] = $departamento->id;
        $data['producto_tipo_id'] = $productoTipo->id;
        $data['tipo_empresa'] = 1;
        $data['comercio_id'] = $comercio->id;
        $url = route('usuarios.registrar');
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }

    /**
     * Login de Usuario
     */
    public function testLogin()
    {
        $url = route('usuarios.login');
        $usuario = factory(User::class)->create();
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, ['email'=>$usuario->email,'password'=>'secret']);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }
}
