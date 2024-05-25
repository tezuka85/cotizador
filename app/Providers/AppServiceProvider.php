<?php

namespace App\Providers;

use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\ServiceProvider;
use Laravel\Passport\Passport;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot(Request $request)
    {
        //Passport::routes();
        Passport::loadKeysFrom(storage_path("cotizaciones/storage") );

    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        $this->createDirectories();
        $this->setEnvironmentValue();
    }

    public function createDirectories(){

        $directories = [
            tmp_path('cotizaciones/bootstrap'),
            tmp_path('cotizaciones/storage/debugbar/app/public'),
            tmp_path('cotizaciones/storage/framework/views'),
            tmp_path('cotizaciones/storage/framework/sessions'),
            tmp_path('cotizaciones/storage/framework/cache/data'),
            tmp_path('cotizaciones/storage/framework/cache'),
            tmp_path('cotizaciones/storage/framework/testing'),
//            tmp_path('cotizaciones/storage/logs'),
            tmp_path('cotizaciones/storage/debugbar'),
            tmp_path('cotizaciones/storage/app'),
            tmp_path('cotizaciones/storage/app/public'),
            tmp_path('cotizaciones/storage/app/web'),
            tmp_path('cotizaciones/bootstrap/cache')
        ];

        //var_dump($directories);

        foreach ($directories as $directory){

            if (!file_exists($directory)) {
                mkdir($directory, 0777, true);
            }

        }
    }

    public function setEnvironmentValue()
    {
        $host = Arr::exists($_SERVER,'HTTP_HOST')?$_SERVER['HTTP_HOST']:'localhost';
        $enviromentAPI = 'test';

        if(strstr($host,'dev')){
            $enviromentAPI = 'test';

        }elseif (strstr($host,'qa')){
            $enviromentAPI = 'qa';

        }elseif (strstr($host,'release')){
            $enviromentAPI = 'release';
        }

        if($enviromentAPI == 'test'){
            putenv("APP_URL=http://$host/t1/");
        }
    }

}
