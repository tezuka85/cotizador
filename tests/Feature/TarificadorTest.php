<?php

namespace Tests\Feature;

use App\ClaroEnvios\Mensajerias\BitacoraCotizacion\BitacoraCotizacionMensajeria;
use App\ClaroEnvios\Mensajerias\GuiaMensajeria;
use App\ClaroEnvios\Mensajerias\Mensajeria;
use App\ClaroEnvios\Mensajerias\MensajeriaCotizable;
use App\User;
use Carbon\Carbon;
use Tests\TestCase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Foundation\Testing\RefreshDatabase;

/**
 * Class TarificadorTest
 * @package Tests\Feature
 */
class TarificadorTest extends TestCase
{
    /**
     * @var
     */
    private $headers;
    /**
     * @var
     */
    private $usuario;

    /**
     *
     */
    protected function setUp()
    {
        parent::setUp();
        $this->usuario = User::first();
        $token = $this->usuario->createToken('AccessToken')->accessToken;
        $this->headers = [
            'Accept'=> 'application/json',
            'Content-Type' => 'Content-Type',
            'Authorization' => 'Bearer '.$token
        ];
    }

    /**
     * Cotizacion de Mensajeria Sin seguro
     *
     * @return void
     */
    public function testCotizacionMensajeriasSinSeguro()
    {
        $data = [
            "codigo_postal_origen"=>11650,
            "codigo_postal_destino"=>11529,
            "tienda"=>1,
            "peso"=>1,
            "largo"=>4,
            "ancho"=>5,
            "alto"=>10,
            "dias_embarque"=>4,
            "comercio"=>$this->usuario->comercio_id,
            "seguro" => 0
        ];
        $url = route('tarificador.cotizarMensajerias');
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertStatus(200)
            ->assertJson(["status"=>"ok","message"=>"Busqueda extisosa!"]);
    }

    /**
     * Cotizacion de Mensajeria con Seguro
     */
    public function testCotizacionMensajeriasConSeguro()
    {
        $data = [
            "codigo_postal_origen"=>11650,
            "codigo_postal_destino"=>11529,
            "tienda"=>1,
            "peso"=>1,
            "largo"=>4,
            "ancho"=>5,
            "alto"=>10,
            "dias_embarque"=>4,
            "seguro"=>1,
            "comercio"=>$this->usuario->comercio_id,
            "valor_paquete" => '1000'
        ];
        $url = route('tarificador.cotizarMensajerias');
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertStatus(200)
            ->assertJson(["status"=>"ok","message"=>"Busqueda extisosa!"]);
    }

    /**
     * Generacion de Guia sin recoleccion
     */
    public function testGenerarGuiaMensajeriaSinRecoleccion()
    {
        $bitacoraCotizacionMensajeria = BitacoraCotizacionMensajeria::doesnthave('bitacorasCotizacionesMensajerias')
            ->orderBy('id', 'desc')
            ->first();
        $data = [
            "nombre_origen"=>"Marcos",
            "apellidos_origen"=>"Orozco",
            "email_origen"=>"marcosorozco.14@gmail.com",
            "calle_origen"=>"Calle 10",
            "numero_origen"=>"1-11",
            "colonia_origen"=>"Reforma Soc",
            "telefono_origen"=>"5529297163",
            "estado_origen"=>"Ciudad de México",
            "municipio_origen"=>"Miguel Hidalgo",
            "referencias_origen"=>"Puerta Blanca",

            "nombre_destino"=>"Ernesto",
            "apellidos_destino"=>"Nolasco",
            "email_destino"=>"marcosorozco.14@gmail.com",
            "calle_destino"=>"Lago Zurich",
            "numero_destino"=>219,
            "colonia_destino"=>"ampliacion granada",
            "telefono_destino"=>"5526277448",
            "estado_destino"=>"Ciudad de Mexico",
            "municipio_destino"=>"Miguel Hidalgo",
            "referencias_destino"=>"edificio"
        ];
        $url = route('tarificador.generarGuiaMensajeria', $bitacoraCotizacionMensajeria->id);
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertStatus(200)
            ->assertJson(["status"=>"ok","message"=>"Guia generada correctamente"]);
    }

    /**
     * Generacion de Guia Con recoleccion
     */
    public function testGenerarGuiaMensajeriaConRecoleccion()
    {
        $bitacoraCotizacionMensajeria = BitacoraCotizacionMensajeria::doesnthave('bitacorasCotizacionesMensajerias')
            ->orderBy('id', 'desc')
            ->first();
        $data = [
            "nombre_origen"=>"Marcos",
            "apellidos_origen"=>"Orozco",
            "email_origen"=>"marcosorozco.14@gmail.com",
            "calle_origen"=>"Calle 10",
            "numero_origen"=>"1-11",
            "colonia_origen"=>"Reforma Soc",
            "telefono_origen"=>"5529297163",
            "estado_origen"=>"Ciudad de México",
            "municipio_origen"=>"Miguel Hidalgo",
            "referencias_origen"=>"Puerta Blanca",

            "nombre_destino"=>"Ernesto",
            "apellidos_destino"=>"Nolasco",
            "email_destino"=>"marcosorozco.14@gmail.com",
            "calle_destino"=>"Lago Zurich",
            "numero_destino"=>219,
            "colonia_destino"=>"ampliacion granada",
            "telefono_destino"=>"5526277448",
            "estado_destino"=>"Ciudad de Mexico",
            "municipio_destino"=>"Miguel Hidalgo",
            "referencias_destino"=>"edificio",
            "generar_recoleccion"=> 1
        ];
        $url = route('tarificador.generarGuiaMensajeria', $bitacoraCotizacionMensajeria->id);
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertStatus(200)
            ->assertJson(["status"=>"ok","message"=>"Guia generada correctamente"]);
    }

    /**
     *
     */
    public function testConsultarGuia()
    {
        $data = [];
        $guiaMensajeria = GuiaMensajeria::orderBy('id', 'desc')->first();
        $url = route('tarificador.consultarGuiaMensajeria', $guiaMensajeria->guia);
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertJsonStructure(["status","message"]);
    }

    /**
     *
     */
    public function testGenerarRecoleccionGuia()
    {
        $data = [];
        $guiaMensajeria = GuiaMensajeria::doesnthave('guiaMensajeriaRecoleccion')
            ->orderBy('id', 'desc')->first();
        $url = route('tarificador.recoleccionService', $guiaMensajeria->guia);
        $response = $this->withHeaders($this->headers)
            ->json('POST', $url, $data);
        $response->assertJsonStructure(["status","message"]);
    }
}
