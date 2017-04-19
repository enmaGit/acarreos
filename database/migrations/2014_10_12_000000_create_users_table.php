<?php

use Carbon\Carbon;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('tipo_usuario', function (Blueprint $table) {
            $table->increments('id');
            $table->string("descripcion", 30);
            $table->timestamps();
        });

        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("tipo_user_id")->unsigned();
            $table->string("login")->unique();
            $table->string('password', 60);
            $table->string("nombre");
            $table->string("apellido");
            $table->string("telefono")->nullable();
            $table->string('email')->unique();
            $table->string('foto')->nullable();
            $table->date('fecha_nac');
            $table->enum('estatus', ['activo', "bloqueado"])
                ->default("activo");
            $table->rememberToken();
            $table->timestamps();

            $table->foreign("tipo_user_id")
                ->references("id")
                ->on("tipo_usuario")
                ->onDelete("cascade");
        });

        Schema::create('tipo_transporte', function (Blueprint $table) {
            $table->increments('id');
            $table->string("nombre");
            $table->text("descripcion");
            $table->timestamps();
        });

        Schema::create('user_transporte', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('transporte_id')->unsigned();
            $table->text("condicion");
            $table->string("foto", 120)->nullable();
            $table->timestamps();

            $table->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");

            $table->foreign("transporte_id")
                ->references("id")
                ->on("tipo_transporte")
                ->onDelete("cascade");
        });

        Schema::create('productos', function (Blueprint $table) {
            $table->increments('id');
            $table->string("nombre");
            $table->text("descripcion");
            $table->integer('comision');
            $table->integer('dias_puja');
            $table->timestamps();

        });

        Schema::create('carrier_producto', function (Blueprint $table) {
            $table->integer('transpor_id')->unsigned();
            $table->integer('producto_id')->unsigned();
            $table->timestamps();

            $table->foreign("producto_id")
                ->references("id")
                ->on("productos")
                ->onDelete("cascade");

            $table->foreign("transpor_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");

        });

        Schema::create('estatus_envio', function (Blueprint $table) {
            $table->increments('id');
            $table->string("descripcion", 30);
            $table->timestamps();

        });

        Schema::create('envios', function (Blueprint $table) {
            $table->increments('id');
            $table->integer("user_id")->unsigned();
            $table->integer('estatus_id')->unsigned()->default(1);
            $table->string('short_descripcion')->default('Envio sin titulo');
            $table->double("lat_origen");
            $table->double("lon_origen");
            $table->double("lat_destino");
            $table->double("lon_destino");
            $table->integer("max_dias");
            $table->date("fecha_pub")->default(Carbon::now());
            $table->date("fecha_res")->nullable();
            $table->date("fecha_fin")->nullable();
            $table->integer("valoracion")->nullable();
            $table->timestamps();

            $table->foreign("user_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");

            $table->foreign("estatus_id")
                ->references("id")
                ->on("estatus_envio")
                ->onDelete("cascade");

        });

        Schema::create('productos_envio', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('envio_id')->unsigned();
            $table->integer('producto_id')->unsigned();
            $table->integer("cantidad")->default(1);
            $table->float("largo");
            $table->float("ancho");
            $table->float("alto");
            $table->float("peso");
            $table->timestamps();

            $table->foreign("envio_id")
                ->references("id")
                ->on("envios")
                ->onDelete("cascade");

            $table->foreign("producto_id")
                ->references("id")
                ->on("productos")
                ->onDelete("cascade");
        });

        Schema::create('ubicacion_envio', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('envio_id')->unsigned();
            $table->double('latitud');
            $table->double("longitud");
            $table->date("fecha_update")->default(Carbon::now());
            $table->timestamps();

            $table->foreign("envio_id")
                ->references("id")
                ->on("envios")
                ->onDelete("cascade");
        });

        Schema::create('ofertas', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('envio_id')->unsigned();
            $table->integer('transpor_id')->unsigned();
            $table->double('precio_puja');
            $table->boolean('ganador')->default(false);
            $table->date("fecha_puja")->default(Carbon::now());
            $table->time("hora_salida");
            $table->time("hora_llegada");
            $table->date("fecha_salida");
            $table->date("fecha_llegada");
            $table->timestamps();

            $table->foreign("envio_id")
                ->references("id")
                ->on("envios")
                ->onDelete("cascade");

            $table->foreign("transpor_id")
                ->references("id")
                ->on("users")
                ->onDelete("cascade");
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::drop('ofertas');
        Schema::drop('ubicacion_envio');
        Schema::drop('productos_envio');
        Schema::drop('envios');
        Schema::drop('estatus_envio');
        Schema::drop('carrier_producto');
        Schema::drop('productos');
        Schema::drop('user_transporte');
        Schema::drop('tipo_transporte');
        Schema::drop('users');
        Schema::drop('tipo_usuario');
    }
}
