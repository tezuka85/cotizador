<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostosMensajeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'costos_mensajerias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cuenta_tipo_id');
                $table->unsignedInteger('negociacion_id');
                $table->integer('asignacion_multiple');
                $table->integer('porcentaje')->nullable();
                $table->decimal('costo')->nullable();
                $table->decimal('porcentaje_seguro')->nullable();
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

                $table->foreign('cuenta_tipo_id')
                    ->references('id')
                    ->on('cuentas_tipos')
                    ->onDelete('restrict');

                $table->foreign('negociacion_id')
                    ->references('id')
                    ->on('negociaciones')
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
        Schema::dropIfExists('costos_mensajerias');
    }
}
