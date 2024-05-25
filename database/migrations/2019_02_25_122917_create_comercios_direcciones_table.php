<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComerciosDireccionesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comercios_direcciones',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('comercio_id');
                $table->unsignedInteger('direccion_tipo_id');
                $table->integer('codigo_postal');
                $table->string('estado');
                $table->string('colonia');
                $table->string('municipio');
                $table->string('calle');
                $table->string('numero');
                $table->text('referencias');
                $table->unsignedInteger('usuario_id');
                $table->unsignedInteger('updated_usuario_id')->nullable();
                $table->timestamps();

                $table->foreign('comercio_id')
                    ->references('id')
                    ->on('comercios')
                    ->onDelete('restrict');

                $table->foreign('direccion_tipo_id')
                    ->references('id')
                    ->on('direcciones_tipos')
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
        Schema::dropIfExists('comercios_direcciones');
    }
}
