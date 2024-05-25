<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateConfiguracionMensajeriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configuracion_mensajerias', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('mensajeria_id');
            $table->unsignedInteger('comercio_id');
            $table->unsignedInteger('negociacion_id');
            $table->smallInteger('tipo_configuracion');
            $table->string('tipo_calculo',3);
            $table->decimal('porcentaje_seguro')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('mensajeria_id')
                ->references('id')
                ->on('mensajerias')
                ->onDelete('restrict');

            $table->foreign('comercio_id')
                ->references('id')
                ->on('comercios')
                ->onDelete('restrict');

            $table->foreign('negociacion_id')
                ->references('id')
                ->on('negociaciones')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configuracion_mensajerias');
    }
}
