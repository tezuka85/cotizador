<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitacorasCotizacionesMensajeriasResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'bitacoras_cotizaciones_mensajerias_response',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('bitacora_cotizacion_mensajeria_id');
                $table->longText('request');
                $table->longText('response');
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('usuarios')
                    ->onDelete('restrict');

                $table->foreign('bitacora_cotizacion_mensajeria_id', 'bcmid_bcmr_fk')
                    ->references('id')
                    ->on('bitacoras_cotizaciones_mensajerias')
                    ->onDelete('restrict');
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bitacoras_cotizaciones_mensajerias_response');
    }
}
