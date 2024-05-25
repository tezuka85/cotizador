<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCuentaNegociacionTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'cuenta_tipo_negociacion',
            function (Blueprint $table) {
                $table->increments('id');
                $table->unsignedInteger('cuenta_tipo_id');
                $table->unsignedInteger('negociacion_id');
                $table->unsignedInteger('usuario_id');
                $table->timestamps();

                $table->foreign('cuenta_tipo_id')
                    ->references('id')
                    ->on('cuentas_tipos')
                    ->onDelete('restrict');

                $table->foreign('negociacion_id')
                    ->references('id')
                    ->on('negociaciones')
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
        Schema::dropIfExists('cuenta_tipo_negociacion');
    }
}
