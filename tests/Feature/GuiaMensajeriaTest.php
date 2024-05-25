<?php

namespace Tests\Feature;

use App\User;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class GuiaMensajeriaTest extends TestCase
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
    public function testResumenEnvios()
    {
        $url = route('guias-mensajerias.resumenEnvios');
        $response = $this->withHeaders($this->headers)
            ->json("POST", $url);
        $response->assertStatus(200)
            ->assertJsonStructure(['status', 'message']);
    }
}
