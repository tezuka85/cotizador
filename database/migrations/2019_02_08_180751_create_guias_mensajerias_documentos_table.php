<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuiasMensajeriasDocumentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'guias_mensajerias_documentos',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('guia_mensajeria_id');
                $table->longText('documento');
                $table->string('extension');
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

                $table->foreign('guia_mensajeria_id')
                    ->references('id')
                    ->on('guias_mensajerias')
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
        Schema::dropIfExists('guias_mensajerias_documentos');
    }
}
