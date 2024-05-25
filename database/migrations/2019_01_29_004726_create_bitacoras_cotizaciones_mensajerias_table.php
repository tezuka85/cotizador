<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBitacorasCotizacionesMensajeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'bitacoras_cotizaciones_mensajerias',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('mensajeria_id');
                //$table->unsignedInteger('tienda_id');
                $table->string('tipo_servicio');
                $table->string('servicio');
                $table->string('costo');
                $table->string('costo_porcentaje');
                $table->string('porcentaje');
                $table->string('costo_convenio');
                $table->double('porcentaje_seguro');
                $table->double('valor_paquete')->default(0);
                $table->string('moneda');
                $table->string('codigo_postal_destino');
                $table->string('codigo_postal_origen');
                $table->string('peso');
                $table->string('largo');
                $table->string('ancho');
                $table->string('alto');
                $table->string('dias_embarque');
                $table->integer('seguro');
                $table->dateTime('fecha_liberacion');
                $table->dateTime('fecha_mensajeria_entrega');
                $table->dateTime('fecha_claro_entrega');
                $table->dateTime('fecha_cotizacion');
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
        Schema::dropIfExists('bitacoras_cotizaciones_mensajerias');
    }
}
