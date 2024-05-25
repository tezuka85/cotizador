<?php
//use \Illuminate\Support\Facades\Facade as Facade;
use Illuminate\Foundation\PackageManifest;
use Illuminate\Filesystem\Filesystem;

/*
|--------------------------------------------------------------------------
| Create The Application
|--------------------------------------------------------------------------
|
| The first thing we will do is create a new Laravel application instance
| which serves as the "glue" for all the components of Laravel, and is
| the IoC container for the system binding all of the various parts.
|
*/

$app = new Illuminate\Foundation\Application(
    realpath(__DIR__.'/../')
);

/*
|--------------------------------------------------------------------------
| Bind Important Interfaces
|--------------------------------------------------------------------------
|
| Next, we need to bind some important interfaces into the container so
| we will be able to resolve them when needed. The kernels serve the
| incoming requests to this application from both the web and CLI.
|
*/
//\Illuminate\Support\Facades\Facade::setFacadeApplication($app);


$path_storage = "/var/cache/php-fpm";
$servicesPath = '/var/cache/php-fpm/cotizaciones/bootstrap/cache/services.php';

$app->useStoragePath($path_storage);

$app->instance(PackageManifest::class, new PackageManifest(
    new Filesystem, $app->basePath(),'/var/cache/php-fpm/cotizaciones/bootstrap/cache/packages.php'
));

$app->instance('path.public',$app->basePath().'/web');

$app->singleton(
    Illuminate\Contracts\Http\Kernel::class,
    App\Http\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Console\Kernel::class,
    App\Console\Kernel::class
);

$app->singleton(
    Illuminate\Contracts\Debug\ExceptionHandler::class,
    App\Exceptions\Handler::class
);

$app->bootstrapPath($path_storage);

$app->useEnvironmentPath(base_path('config/'));
/*
|--------------------------------------------------------------------------
| Return The Application
|--------------------------------------------------------------------------
|
| This script returns the application instance. The instance is given to
| the calling script so we can separate the building of the instances
| from the actual running of the application and sending responses.
|
*/

return $app;
