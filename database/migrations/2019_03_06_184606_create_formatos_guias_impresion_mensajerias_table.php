<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormatosGuiasImpresionMensajeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'formatos_guias_impresion_mensajerias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('mensajeria_id');
                $table->unsignedInteger('formato_guia_impresion_id');
                $table->integer('default')->default(0);
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

                $table->foreign('mensajeria_id')
                    ->references('id')
                    ->on('mensajerias')
                    ->onDelete('restrict');

                $table->foreign('formato_guia_impresion_id', 'fgiid_fgim_fk')
                    ->references('id')
                    ->on('formatos_guias_impresion')
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
        Schema::dropIfExists('formatos_guias_impresion_mensajerias');
    }
}
