<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostosMensajeriasPorcentajesAuditoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'costos_mensajerias_porcentajes_auditorias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('funcion');
                $table->unsignedInteger('costo_mensajeria_porcentaje_id');
                $table->unsignedInteger('costo_mensajeria_id');
                $table->unsignedInteger('mensajeria_id');
                $table->unsignedInteger('comercio_id');
                $table->integer('porcentaje')->default(0);
                $table->double('costo')->default(0);
                $table->double('porcentaje_seguro');
                $table->unsignedInteger('usuario_id');
                $table->unsignedInteger('updated_usuario_id')->nullable();
                $table->timestamps();
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
        Schema::dropIfExists('costos_mensajerias_porcentajes_auditorias');
    }
}
