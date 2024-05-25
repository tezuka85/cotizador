<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfiguracionesMensajeriasUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'configuraciones_mensajerias_usuarios',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('mensajeria_id');
                $table->unsignedInteger('formato_guia_impresion_id')->nullable();
                $table->unsignedInteger('usuario_id');
                $table->unsignedInteger('updated_usuario_id')->nullable();
                $table->timestamps();

                $table->foreign('mensajeria_id')
                    ->references('id')
                    ->on('mensajerias')
                    ->onDelete('restrict');

                $table->foreign('formato_guia_impresion_id', 'fgiid_cmu_fk')
                    ->references('id')
                    ->on('formatos_guias_impresion')
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
        Schema::dropIfExists('configuraciones_mensajerias_usuarios');
    }
}
