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

use App\Envio;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

Route::get('/', function () {
    return view('welcome');
});

Route::get('/todobien', function () {

    /*$envios = Envio::where('estatus_id', 1)->orderBy('fecha_pub','desc')->get();
    $arrayEnviosVencidos = [];
    foreach ($envios as $envio) {
        if ($envio->estaVencido()) {
            $arrayEnviosVencidos[] = $envio;
        }
    }*/
    /*$envio = Envio::where('estatus_id', 1)->orderBy('fecha_pub', 'desc')->first();
    $fechaBorrar = Carbon::now()->subDays($envio->diasPuja());
    $fechaPublicacion = Carbon::createFromFormat('Y-m-d', $envio->fecha_pub);
    $diferencia = $fechaBorrar->diffInDays($fechaPublicacion) < 0;*/
    //return response()->json(compact('arrayEnviosVencidos'), 200);
    return response('msg', 200);
});

Route::get('password/email', 'Auth\PasswordController@getEmail');
Route::post('password/email', 'Auth\PasswordController@postEmail');

// Password reset routes...
Route::get('password/reset/{token}', 'Auth\PasswordController@getReset');
Route::post('password/reset', 'Auth\PasswordController@postReset');

Route::get('/hey/{id}', function ($id) {
    $user = User::find($id);
    $notification = new \App\Helpers\PushHandler;
    $data = ['msg' => 'Su paquete ha llegado al destino',
        'tipo' => Config::get('constants.NOTIF_ENVIO_FINALIZADO'),
        'envio' => Envio::with('user')->where('id', 1)->first()->toJson()];
    $envio = Envio::find(3);
    $ret = Carbon::now();
    return response()->json(compact('ret'), 200);
    return $notification->generatePush($user, $data);
});

Route::post('/api/v1/spam', function (Request $request) {
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

    if ($user->isAnAdmin()) {
        foreach (User::all() as $user) {
            $notification = new \App\Helpers\PushHandler;
            $data = ['msg' => $request->input('mensaje'),
                'tipo' => Config::get('constants.NOTIF_SPAM')];
            $notification->generatePush($user, $data);
        }
    }

    // the token is valid and we have found the user via the sub claim
    return response()->json(compact('user'));
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

    //Route::group(['middleware' => 'jwt.refresh'], function () {

    Route::get('notificacion/{id}', function ($id) {
        //TODO revisar que el token coincida con el del transportista ganador
        $envio = Envio::find($id);
        if ($envio != null) {
            $notification = new \App\Helpers\PushHandler;
            $data = ['msg' => 'Su paquete ha llegado al destino',
                'tipo' => Config::get('constants.NOTIF_ENVIO_FINALIZADO'),
                'envio' => $envio->toJson()];
            $notification->generatePush($envio->user_id->id_push, $data);
            return response()->json(compact('envio'), 200);
        }
        $error = array(
            'error' => 'No se encuentra un envio con ese codigo'
        );
        return response()->json($error, 404);
    });

    Route::resource('user', 'UserController',
        ['except' => ['create', 'store', 'edit']]);

    Route::resource('transportista.transporte', 'UserTransporteController',
        ['except' => ['create', 'edit']]);

    Route::resource('transportista.producto', 'UserProductoController',
        ['except' => ['create', 'show', 'edit', 'update']]);

    Route::resource('tipo_producto', 'TipoProductoController',
        ['except' => ['create', 'edit']]);

    Route::resource('tipo_transporte', 'TipoTransporteController',
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


