<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AgregarCamposFormularios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->enum('tipo_dni', ['1', '2', '3', '4'])->default('1');
            $table->string('dni')->nullable();
            $table->enum('tipo_licencia', ['A', 'B', 'C', 'D', 'E', 'E1 a', 'E2 b', 'E3 c', 'F', 'G', 'H', 'I'])->default('A');
            $table->string('num_seguridad')->nullable();
            $table->unique('dni');
            $table->unique('num_seguridad');
        });
        Schema::table('productos_envio', function (Blueprint $table) {
            $table->text('descripcion')->nullable();
        });
        Schema::table('user_transporte', function (Blueprint $table) {
            $table->string('placa')->nullable();
            $table->string('poliza_compa')->default("xxxxxxx");
            $table->string('poliza_numero')->default("0000000");
            $table->unique('placa');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        //
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('tipo_dni');
            $table->dropColumn('dni');
            $table->dropColumn('tipo_licencia');
            $table->dropColumn('num_seguridad');
        });
        Schema::table('productos_envio', function (Blueprint $table) {
            $table->dropColumn('descripcion');
        });
        Schema::table('user_transporte', function (Blueprint $table) {
            $table->dropColumn('placa');
            $table->dropColumn('poliza_compa');
            $table->dropColumn('poliza_numero');
        });
    }
}
