<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEventosGuiasMensajeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'eventos_guias_mensajerias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('guia_mensajeria_id');
                $table->string('codigo');
                $table->string('evento');
                $table->string('ubicacion');
                $table->dateTime('fecha');
                $table->timestamps();

                $table->foreign('guia_mensajeria_id')
                    ->references('id')
                    ->on('guias_mensajerias')
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
        Schema::dropIfExists('eventos_guias_mensajerias');
    }
}
