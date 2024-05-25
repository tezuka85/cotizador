<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitacorasMensajeriasOrigenTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'bitacoras_mensajerias_origen',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('nombre');
                $table->string('apellidos');
                $table->string('email');
                $table->string('calle');
                $table->string('numero');
                $table->string('colonia');
                $table->string('municipio');
                $table->string('telefono');
                $table->string('estado');
                $table->string('referencias');
                $table->unsignedInteger('usuario_id');
                $table->unsignedInteger('updated_usuario_id')->nullable();
                $table->timestamps();

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
        Schema::dropIfExists('bitacoras_mensajerias_origen');
    }
}
