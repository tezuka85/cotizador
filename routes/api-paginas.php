<?php

use Illuminate\Http\Request;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::prefix('pgs')->group(function () {

    Route::middleware(['jwt.auth', 'checkrole:superadministrador,comercio'])->group(
        function () {
            Route::name('tarificador.')->group(
                function () {
                    Route::post(
                        'mensajeria-cotizador',
                        'Com\CotizadorController@cotizarMensajerias'
                    )->name('cotizador.cotizarMensajerias');
                }
            );
        }
    );
});
