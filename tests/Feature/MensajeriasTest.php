<?php

namespace Tests\Feature;

use App\ClaroEnvios\Mensajerias\Mensajeria;
use App\ClaroEnvios\Mensajerias\MensajeriaCotizable;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MensajeriasTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testMensajeriasConInterface()
    {
        $mensajerias = Mensajeria::all();
        foreach ($mensajerias as $mensajeria) {
            $mensajeriaEmpresa = new $mensajeria->clase();
            $this->assertTrue($mensajeriaEmpresa instanceof MensajeriaCotizable);
        }
    }
}
