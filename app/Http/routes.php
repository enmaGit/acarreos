<?php

/*
|--------------------------------------------------------------------------
| Application Routes
|--------------------------------------------------------------------------
|
| Here is where you can register all of the routes for an application.
| It's a breeze. Simply tell Laravel the URIs it should respond to
| and give it the controller to call when that URI is requested.
|
*/

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Tymon\JWTAuth\Facades\JWTAuth;

Route::get('/', function () {
    return view('welcome');
});

Route::post('/home', function () {

    try {
        if (!$user = JWTAuth::parseToken()->authenticate()) {
            return response()->json(['user_not_found'], 404);
        }

    } catch (Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

        return response()->json(['token_expired'], $e->getStatusCode());

    } catch (Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

        return response()->json(['user_not_found'], 401);

    } catch (Tymon\JWTAuth\Exceptions\JWTException $e) {

        return response()->json(['token_absent'], $e->getStatusCode());

    }

    // the token is valid and we have found the user via the sub claim
    return response()->json(compact('user'));
});

Route::group(['prefix' => 'api/v1'], function () {

    //TODO añadir parámetros de búsqueda a user para buscar clientes y trans
    //TODO añadir parámetros de búsquedas a los envíos

    //Route::group(['middleware' => 'jwt.refresh'], function () {

    Route::resource('user', 'UserController',
        ['except' => ['create', 'store', 'edit']]);


    Route::resource('transportista.transporte', 'UserTransporteController',
        ['except' => ['create', 'edit']]);

    Route::resource('transportista.producto', 'UserProductoController',
        ['except' => ['create', 'show', 'edit', 'update']]);

    Route::resource('tipo_producto', 'TipoProductoController',
        ['except' => ['create', 'edit']]);

    Route::get('cliente', function (Request $request) {
        $parameters = $request->all();
        $parameters['tipo_user_id'] = '2';
        return Redirect::route('api.v1.user.index', $parameters);
    });

    Route::get('transportista', function (Request $request) {
        $parameters = $request->all();
        $parameters['tipo_user_id'] = '3';
        return Redirect::route('api.v1.user.index', $parameters);
    });

    Route::resource('cliente.envio', 'UserEnviosController',
        ['except' => ['create', 'show', 'edit', 'destroy']]);

    Route::resource('envio', 'EnvioController',
        ['except' => ['create', 'edit', 'store', 'update', 'destroy']]);

    Route::resource('envio.producto', 'EnvioProductoController',
        ['except' => ['create', 'edit']]);

    Route::resource('envio.ubicacion', 'EnvioUbicacionController',
        ['except' => ['create', 'edit', 'show']]);

    Route::resource('envio.ganador', 'EnvioGanadorController',
        ['except' => ['create', 'edit', 'show']]);

    Route::resource('envio.oferta', 'EnvioOfertaController',
        ['except' => ['create', 'edit']]);

    Route::resource('comision', 'ComisionController',
        ['except' => ['create', 'edit', 'show']]);

    //});

    Route::post('auth/login', ['as' => 'login', 'uses' => 'Auth\AuthController@postLogin']);

    Route::get('auth/logout', ['as' => 'logout', 'uses' => 'Auth\AuthController@getLogout']);

    Route::post('auth/register', ['as' => 'register', 'uses' => 'Auth\AuthController@postRegister']);

});


