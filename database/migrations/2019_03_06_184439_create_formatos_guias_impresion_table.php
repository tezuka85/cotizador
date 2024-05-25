<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFormatosGuiasImpresionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'formatos_guias_impresion',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('clave', 9);
                $table->string('descripcion');
                $table->string('extension');
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

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
        Schema::dropIfExists('formatos_guias_impresion');
    }
}
