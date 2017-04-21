<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Carbon\Carbon;

class AddInfoEnvios extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::table('envios', function (Blueprint $table) {
            $table->date("fecha_sug")->default(Carbon::now());
            $table->time("hora_sug")->default('07:12:54');
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
        Schema::table('envios', function (Blueprint $table) {
            $table->dropColumn("fecha_sug");
            $table->dropColumn("hora_sug");
        });
    }
}
