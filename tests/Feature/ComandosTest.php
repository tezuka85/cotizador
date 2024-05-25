<?php

namespace Tests\Feature;

use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

class ComandosTest extends TestCase
{
    /**
     * A basic test example.
     *
     * @return void
     */
    public function testMensajeriaHistoricoEventos()
    {
        $this->artisan('mensajeria:historicoEventos')
            ->assertExitCode(0);
    }
}
