<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddComercioIdUsuariosTable extends Migration
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
                $table->unsignedInteger('comercio_id')
                    ->after('departamento_id')
                    ->nullable();

                $table->foreign('comercio_id')
                    ->references('id')
                    ->on('comercios')
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
                $table->dropForeign(['comercio_id']);
                $table->dropColumn('comercio_id');
            }
        );
    }
}
