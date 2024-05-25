<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddCostosMensajeriasPorcentajesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'costos_mensajerias_porcentajes',
            function (Blueprint $table) {
                $table->unsignedInteger('costo_mensajeria_id')
                    ->after('id');

                $table->foreign('costo_mensajeria_id')
                    ->references('id')
                    ->on('costos_mensajerias')
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
        Schema::table(
            'costos_mensajerias_porcentajes',
            function (Blueprint $table) {
                $table->dropForeign(['costo_mensajeria_id']);
                $table->dropColumn('costo_mensajeria_id');
            }
        );
    }
}
