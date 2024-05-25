<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComandosEjecucionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'comandos_ejecucion',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('comando_id');
                $table->dateTime('fecha_inicio');
                $table->dateTime('fecha_fin');
                $table->timestamps();

                $table->foreign('comando_id')
                    ->references('id')
                    ->on('comandos')
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
        Schema::dropIfExists('comandos_ejecucion');
    }
}
