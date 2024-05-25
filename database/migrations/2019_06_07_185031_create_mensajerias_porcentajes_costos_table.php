<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMensajeriasPorcentajesCostosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mensajerias_porcentajes_costos', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('configuracion_id');
            $table->integer('porcentaje');
            $table->decimal('costo');
            $table->timestamps();

            $table->foreign('configuracion_id')
                ->references('id')
                ->on('configuracion_mensajerias')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('mensajerias_porcentajes_costos');
    }
}
