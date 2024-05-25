<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateAccesosComerciosMensajeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'accesos_comercios_mensajerias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('acceso_campo_mensajeria_id');
                $table->unsignedInteger('mensajeria_id');
                $table->unsignedInteger('comercio_id');
                $table->string('valor');
                $table->timestamps();

                $table->foreign('acceso_campo_mensajeria_id')
                    ->references('id')
                    ->on('accesos_campos_mensajerias')
                    ->onDelete('restrict');

                $table->foreign('mensajeria_id')
                    ->references('id')
                    ->on('mensajerias')
                    ->onDelete('restrict');

                $table->foreign('comercio_id')
                    ->references('id')
                    ->on('comercios')
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
        Schema::dropIfExists('accesos_comercios_mensajerias');
    }
}
