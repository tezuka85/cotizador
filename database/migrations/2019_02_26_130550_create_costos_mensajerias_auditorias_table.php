<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostosMensajeriasAuditoriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'costos_mensajerias_auditorias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('funcion');
                $table->unsignedInteger('costo_mensajeria_id');
                $table->unsignedInteger('cuenta_tipo_id');
                $table->unsignedInteger('negociacion_id');
                $table->integer('asignacion_multiple');
                $table->integer('porcentaje')->nullable();
                $table->decimal('costo')->nullable();
                $table->decimal('porcentaje_seguro')->nullable();
                $table->unsignedInteger('usuario_id');
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
        Schema::dropIfExists('costos_mensajerias_auditorias');
    }
}
