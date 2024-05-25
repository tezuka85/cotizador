<?php

/**
 *  Si se cambia algo de la siguiente ruta, se debe considerar el cambio en :
 *  app/Providers/ApplicationProvider.php
 */

use App\ClaroEnvios\S3\S3Acciones;

if (! function_exists('tmp_path')) {
    function tmp_path($path)
    {
        return '/var/cache/php-fpm/'.$path;
    }
}

if (! function_exists('storage_path')) {
    function storage_path($path)
    {
        return '/var/cache/php-fpm/'.$path;
    }
}

if (! function_exists('config_path')) {
    function config_path($path = '')
    {
        return app()->basePath('/var/cache/php-fpm') . ($path ? DIRECTORY_SEPARATOR . $path : $path);
    }
}

if(!function_exists('getCachedServicesPath')){
    function getCachedServicesPath(){
        return '/var/cache/php-fpm/cotizaciones/bootstrap/cache/services.php';
    }
}

/*if (! function_exists('create_role_name')) {
    function create_role_name(\App\Models\Area $area, $name_role)
    {
        return snake_case(strtolower(trim($area->name)).'_'.strtolower(trim($name_role)));
    }
}*/

/*function diaRecoleccion()
{
    $diaPickup = new \Illuminate\Support\Carbon();
    $revisaDia = date('N');

    if ($revisaDia == 5) {
        $diaPickup->addHours(3)->addDays(3);
    } elseif ($revisaDia == 6){
        $diaPickup->addHours(3)->addDays(2);
    } else {
        $diaPickup->addDays(1);
    }

    return $diaPickup;
}

function conversionCentimetros($unidad, $valor)
{
    switch ($unidad) {
        case 'IN':
            $valor = $valor * 2.54;
            break;

    }

    return $valor;
}

function conversionKilogramos($unidad, $valor)
{
    switch ($unidad) {
        case 'LB':
            $valor = $valor * 0.453592;
            break;

    }

    return $valor;
}

function subirS3($nombreArchivo, $ruta,$uria){

    $s3config = (config('filesystems.disks.s3')) ? config('filesystems.disks.s3') : array();
    //Subir al S3 Amazon los archivos
    $S3Acciones = new S3Acciones($s3config);
    $anio = date("Y");

    $s3 = $S3Acciones->SubirAS3($ruta, $nombreArchivo, $uria);

    return $s3;
}
*/