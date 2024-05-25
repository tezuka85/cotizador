<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddColumnTokenBitacorasCotizacionesMensajerias extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('bitacoras_cotizaciones_mensajerias', function (Blueprint $table) {
            $table->string('token',200)
                ->unique()
                ->nullable()
                ->after('updated_usuario_id');

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
               Schema::table('bitacoras_cotizaciones_mensajerias', function (Blueprint $table) {
               $table->dropColumn('token');
           }
       );
    }
}
