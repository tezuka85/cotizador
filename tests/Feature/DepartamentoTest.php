<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DepartamentoTest extends TestCase
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
     * A basic test example.
     *
     * @return void
     */
    public function testIndex()
    {
        $url = route('departamentos.index');
        $response = $this->withHeaders($this->headers)
            ->json('GET', $url, []);
        $response->assertStatus(200)
            ->assertJson(["status"=>"ok","message"=>"Busqueda exitosa!"]);
    }
}
