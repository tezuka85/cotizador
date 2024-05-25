<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDepartamentoIdUsuariosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(
            'usuarios',
            function (Blueprint $table) {
                $table->unsignedInteger('departamento_id')
                    ->nullable()
                    ->after('email');

                $table->foreign('departamento_id')
                    ->references('id')
                    ->on('departamentos')
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
            'usuarios',
            function (Blueprint $table) {
                $table->dropForeign(['departamento_id']);
                $table->dropColumn('departamento_id');
            }
        );
    }
}
