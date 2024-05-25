<?php

namespace App\Providers;

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Route;
use KeycloakGuard\KeycloakGuard;
use Laravel\Passport\Passport;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     *
     * @var array
     */
    protected $policies = [
        //'App\Model' => 'App\Policies\ModelPolicy',
    ];

    /**
     * Register any authentication / authorization services.
     *
     * @return void
     */
    public function boot()
    {
        $this->registerPolicies();

//        // add custom guard
//        Auth::extend('pgs', function ($app, $name, array $config) {
//            return new KeycloakGuard(Auth::createUserProvider($config['provider'], $app->make('request')));
//        });

        Route::group(['prefix' => 'api', 'middleware' => 'cors'], function() {
            Passport::tokensCan([
                'admin' => 'acciones administrador',
                'comercio' => 'acciones comercios',
            ]);

            //Passport::routes();

        });
    }
}
