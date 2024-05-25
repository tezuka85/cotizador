<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuiasMensajeriasRecoleccionesResponseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'guias_mensajerias_recolecciones_response',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('guia_mensajeria_recoleccion_id');
                $table->text('request');
                $table->text('response');
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

                $table->foreign('guia_mensajeria_recoleccion_id', 'gmrr_gmrid_fk')
                    ->references('id')
                    ->on('guias_mensajerias_recolecciones')
                    ->onDelete('restrict');

                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('usuarios')
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
        Schema::dropIfExists('guias_mensajerias_recolecciones_response');
    }
}
