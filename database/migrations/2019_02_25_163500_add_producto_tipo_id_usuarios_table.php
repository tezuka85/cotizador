<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddProductoTipoIdUsuariosTable extends Migration
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
                $table->unsignedInteger('producto_tipo_id')
                    ->after('comercio_id')->nullable();

                $table->unsignedInteger('tipo_empresa')
                    ->after('producto_tipo_id')->nullable();

                $table->foreign('producto_tipo_id')
                    ->references('id')
                    ->on('productos_tipos')
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
                $table->dropForeign(['producto_tipo_id']);
                $table->dropColumn('producto_tipo_id');
                $table->dropColumn('tipo_empresa');
            }
        );
    }
}
