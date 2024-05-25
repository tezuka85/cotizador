<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuiasMensajeriasRecoleccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'guias_mensajerias_recolecciones',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('guia_mensajeria_id');
                $table->unsignedInteger('pick_up');
                $table->string('localizacion')->nullable();
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('usuarios')
                    ->onDelete('restrict');

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
        Schema::dropIfExists('guias_mensajerias_recolecciones');
    }
}
