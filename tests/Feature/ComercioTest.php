<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComercioTest extends TestCase
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
     * A basic test example.
     *
     * @return void
     */
    public function testComercio()
    {
        $data = [
            "clave"=>"COMP_001",
	        "descripcion"=>"PORTAL",
	        "direccion_tipo_id"=>1,
            "codigo_postal"=>11650,
            "envios_promedio"=>20,
	        "estado"=>"Ciudad de México",
	        "colonia"=>"Reforma Soc",
            "municipio"=>"Miguel Hidalgo",
	        "calle"=>"Calle 10",
	        "numero"=>"1-11",
	        "referencias"=>"Comercio"
        ];
        $url = route('comercios.store');
        $response = $this->withHeaders($this->headers)
            ->json("POST", $url, $data);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }
}
