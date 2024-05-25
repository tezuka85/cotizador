<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateGuiasMensajeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'guias_mensajerias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('guia')->index();
                $table->unsignedInteger('bitacora_cotizacion_mensajeria_id');
                $table->unsignedInteger('bitacora_mensajeria_origen_id');
                $table->unsignedInteger('bitacora_mensajeria_destino_id');
                $table->integer('status_entrega')->default(1);
                $table->dateTime('fecha_status_entrega')->nullable();
                $table->unsignedInteger('usuario_id');
                $table->unsignedInteger('updated_usuario_id')->nullable();
                $table->timestamps();

                $table->foreign('bitacora_cotizacion_mensajeria_id', 'bcmid_gm_fk')
                    ->references('id')
                    ->on('bitacoras_cotizaciones_mensajerias')
                    ->onDelete('restrict');

                $table->foreign('bitacora_mensajeria_origen_id', 'bmoid_gm_fk')
                    ->references('id')
                    ->on('bitacoras_mensajerias_origen')
                    ->onDelete('restrict');

                $table->foreign('bitacora_mensajeria_destino_id', 'bmdid_gm_fk')
                    ->references('id')
                    ->on('bitacoras_mensajerias_destino')
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
        Schema::dropIfExists('guias_mensajerias');
    }
}
