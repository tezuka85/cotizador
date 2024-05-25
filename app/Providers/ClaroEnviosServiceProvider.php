<?php

namespace App\Providers;

use App\ClaroEnvios\Mensajerias\CostoMensajeria;
use App\ClaroEnvios\Mensajerias\CostoMensajeriaPorcentaje;
use App\Observers\Mensajeria\CostoMensajeriaObserver;
use App\Observers\Mensajeria\CostoMensajeriaPorcentajeObserver;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\ServiceProvider;

class ClaroEnviosServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap services.
     *
     * @return void
     */
    public function boot()
    {
        Schema::defaultStringLength(191);
        CostoMensajeria:CostoMensajeria::observe(CostoMensajeriaObserver::class);
        CostoMensajeriaPorcentaje::observe(CostoMensajeriaPorcentajeObserver::class);
    }

    /**
     * Register services.
     *
     * @return void
     */
    public function register()
    {
        #Mensajerias
        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\MensajeriaServiceInterface',
            'App\ClaroEnvios\Mensajerias\MensajeriaValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\MensajeriaRepositoryInterface',
            'App\ClaroEnvios\Mensajerias\MensajeriaRepository'
        );

        #Historico de Eventos
        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\HistoricoEvento\MensajeriaHistoricoEventoServiceInterface',
            'App\ClaroEnvios\Mensajerias\HistoricoEvento\MensajeriaHistoricoEventoValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\HistoricoEvento\MensajeriaHistoricoEventoRepositoryInterface',
            'App\ClaroEnvios\Mensajerias\HistoricoEvento\MensajeriaHistoricoEventoRepository'
        );

        #Comandos
        $this->app->bind(
            'App\ClaroEnvios\Comandos\ComandoServiceInterface',
            'App\ClaroEnvios\Comandos\ComandoService'
        );

        $this->app->bind(
            'App\ClaroEnvios\Comandos\ComandoRepositoryInterface',
            'App\ClaroEnvios\Comandos\ComandoRepository'
        );

        #Usuario
        $this->app->bind(
            'App\ClaroEnvios\Usuarios\UsuarioServiceInterface',
            'App\ClaroEnvios\Usuarios\UsuarioValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\Usuarios\UsuarioRepositoryInterface',
            'App\ClaroEnvios\Usuarios\UsuarioRepository'
        );

        #Departamentos
        $this->app->bind(
            'App\ClaroEnvios\Departamentos\DepartamentoServiceInterface',
            'App\ClaroEnvios\Departamentos\DepartamentoValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\Departamentos\DepartamentoRepositoryInterface',
            'App\ClaroEnvios\Departamentos\DepartamentoRepository'
        );

        #CuentaTipo
        $this->app->bind(
            'App\ClaroEnvios\CuentaTipo\CuentaTipoServiceInterface',
            'App\ClaroEnvios\CuentaTipo\CuentaTipoValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\CuentaTipo\CuentaTipoRepositoryInterface',
            'App\ClaroEnvios\CuentaTipo\CuentaTipoRepository'
        );

        #Costo Mensajeria
        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\Costo\CostoMensajeriaServiceInterface',
            'App\ClaroEnvios\Mensajerias\Costo\CostoMensajeriaValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\Costo\CostoMensajeriaRepositoryInterface',
            'App\ClaroEnvios\Mensajerias\Costo\CostoMensajeriaRepository'
        );

        #Negociacion
        $this->app->bind(
            'App\ClaroEnvios\Negociacion\NegociacionServiceInterface',
            'App\ClaroEnvios\Negociacion\NegociacionValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\Negociacion\NegociacionRepositoryInterface',
            'App\ClaroEnvios\Negociacion\NegociacionRepository'
        );

        #Comercio
        $this->app->bind(
            'App\ClaroEnvios\Comercios\ComercioServiceInterface',
            'App\ClaroEnvios\Comercios\ComercioValidator'
        );

        $this->app->bind(
            'App\ClaroEnvios\Comercios\ComercioRepositoryInterface',
            'App\ClaroEnvios\Comercios\ComercioRepository'
        );

        #Mensajerias auditorias
        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaAuditoriaServiceInterface',
            'App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaAuditoriaService'
        );

        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaAuditoriaRepositoryInterface',
            'App\ClaroEnvios\Mensajerias\Auditorias\CostoMensajeriaAuditoriaRepository'
        );

        #Accesos Mensajerias
        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\Accesos\AccesoMensajeriaServiceInterface',
            'App\ClaroEnvios\Mensajerias\Accesos\AccesoMensajeriaService'
        );

        $this->app->bind(
            'App\ClaroEnvios\Mensajerias\Accesos\AccesoMensajeriaRepositoryInterface',
            'App\ClaroEnvios\Mensajerias\Accesos\AccesoMensajeriaRepository'
        );
    }
}
