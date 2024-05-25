<?php

namespace App\Providers;

use Illuminate\Support\Facades\Route;
use Illuminate\Foundation\Support\Providers\RouteServiceProvider as ServiceProvider;

class RouteServiceProvider extends ServiceProvider
{
    /**
     * This namespace is applied to your controller routes.
     *
     * In addition, it is set as the URL generator's root namespace.
     *
     * @var string
     */
    protected $namespace = 'App\Http\Controllers';

    /**
     * Define your route model bindings, pattern filters, etc.
     *
     * @return void
     */
    public function boot()
    {
        $pDate1 =  '^(\d{4})(-)(0[1-9]|1[0-2])(-)(0[1-9]|1[0-9]|2[0-9]|3[0-1])$';
        Route::pattern('fecha_inicio',$pDate1);
        Route::pattern('fecha_fin', $pDate1);
        Route::pattern('mensajeria_id', '[0-9]+');
//        Route::pattern('comercio_id', '[0-9]+');
        Route::pattern('status_entrega', '\d{1,3}');

        parent::boot();
    }

    /**
     * Define the routes for the application.
     *
     * @return void
     */
    public function map()
    {
        $this->mapApiRoutes();

        //$this->mapWebRoutes();
        $this->mapApiPaginasRoutes();
        //$this->mapApiGeotrackRoutes();

    }

    /**
     * Define the "web" routes for the application.
     *
     * These routes all receive session state, CSRF protection, etc.
     *
     * @return void
     */
    /*protected function mapWebRoutes()
    {
        Route::middleware('web')
             ->namespace($this->namespace)
             ->group(base_path('routes/web.php'));
    }*/

    /**
     * Define the "api" routes for the application.
     *
     * These routes are typically stateless.
     *
     * @return void
     */
    protected function mapApiRoutes()
    {
        Route::prefix('t1')
             ->middleware('api')
             ->namespace($this->namespace)
             ->group(base_path('routes/api.php'));
    }

    protected function mapApiPaginasRoutes()
    {
        Route::prefix('t1')
            ->namespace($this->namespace)
            ->group(base_path('routes/api-paginas.php'));
    }

    /*protected function mapApiGeotrackRoutes()
    {
        Route::prefix('t1')
            ->namespace($this->namespace)
            ->group(base_path('routes/api-geotrack.php'));
    }*/
}
