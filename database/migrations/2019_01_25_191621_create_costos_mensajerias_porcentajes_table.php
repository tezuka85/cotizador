<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCostosMensajeriasPorcentajesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'costos_mensajerias_porcentajes',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('mensajeria_id');
                $table->integer('porcentaje')->default(0);
                $table->double('costo')->default(0);
                $table->double('porcentaje_seguro');
                $table->unsignedInteger('usuario_id');
                $table->unsignedInteger('updated_usuario_id')->nullable();
                $table->timestamps();

                $table->foreign('mensajeria_id')
                    ->references('id')
                    ->on('mensajerias')
                    ->onDelete('restrict');

                $table->foreign('usuario_id')
                    ->references('id')
                    ->on('usuarios')
                    ->onDelete('restrict');

                $table->foreign('updated_usuario_id')
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
        Schema::dropIfExists('costos_mensajerias_porcentajes');
    }
}
