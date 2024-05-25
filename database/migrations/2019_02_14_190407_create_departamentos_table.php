<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDepartamentosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create(
            'departamentos',
            function (Blueprint $table) {
                $table->increments('id');
                $table->string('clave', 9);
                $table->string('descripcion');
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
        Schema::dropIfExists('departamentos');
    }
}
