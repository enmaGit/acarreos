<?php

namespace App\Http\Controllers;

use App\Envio;
use App\EstatusEnvio;
use App\Producto;
use App\TipoTransporte;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Tymon\JWTAuth\Facades\JWTAuth;
use Carbon\Carbon;

class UserEnviosController extends Controller
{

    public function __construct()
    {
        $this->middleware('jwt.auth', ['except' => ['getLogout', 'postRegister']]);
    }

    /**
     * Display a listing of the resource.
     *
     * @return Response
     */
    public function index(Request $request, $idUsuario)
    {
        //TODO habilitar paginado
        $user = User::find($idUsuario);
        if (!$user) {
            $error = array(
                'error' => 'No se encuentra un usuario con ese codigo'
            );
            return response()->json($error, 404);
        }
        //Filtrar por estatus
        if ($user->isATransportist()) {
            //TODO devolver los envios donde el transpor es el ganador
            if ($request->has('estatus')) {
                $estatus = $request->input('estatus');
                $listaDeEstatus = explode(',', $estatus);
                $envios = Envio::whereHas('ofertas', function ($query) use ($user) {
                    $query->where('transpor_id', $user->id)
                        ->where('ganador', true);
                })
                    ->whereIn('estatus_id', $listaDeEstatus)
                    ->with(array('user' => function ($query) {
                        $query->select('id', 'login');
                    }))
                    ->with('estatus')
                    ->with('ofertas')
                    ->orderBy('fecha_pub', 'desc')
                    ->simplePaginate(10);
                return response()->json($envios->items(), 200);
            }
        }
        if ($request->has('estatus')) {
            $estatus = $request->input('estatus');
            $listaDeEstatus = explode(',', $estatus);
            $envios = $user->envios()
                ->whereIn('estatus_id', $listaDeEstatus)
                ->with(array('user' => function ($query) {
                    $query->select('id', 'login');
                }))
                ->with('estatus')
                ->with('ofertas')
                ->orderBy('fecha_pub', 'desc')
                ->simplePaginate(10);
            return response()->json($envios->items(), 200);
        }
        $envios = $user->envios()->with('estatus')->get();
        return response()->json($envios, 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public function store(Request $request, $idUsuario)
    {
        //
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {
            $parameters = $request->all();
            $parameters['user_id'] = $idUsuario;
            $validator = Validator::make($parameters, [
                'short_descripcion' => 'required|max:50',
                'productos' => 'array',
                'transportes' => 'array',
                'lat_origen' => 'required|numeric',
                'lon_origen' => 'required|numeric',
                'ref_origen' => 'required',
                'lat_destino' => 'required|numeric',
                'lon_destino' => 'required|numeric',
                'ref_destino' => 'required',
                'fecha_sug' => 'required|date',
                'hora_sug' => array('required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'),
                'max_dias' => 'integer',
                'user_id' => 'required|exists:users,id,tipo_user_id,2'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $productos = $request->input('productos');
            $transportes = $request->input('transportes');

            /*if (sizeof($productos) < ) {
                return response()->json(['error' => 'Should have at least one product'], 400);
            }*/

            $user = User::find($idUsuario);
            $envio = new Envio();
            $envio->short_descripcion = $parameters['short_descripcion'];
            $envio->lat_origen = $parameters['lat_origen'];
            $envio->lon_origen = $parameters['lon_origen'];
            $envio->fecha_pub = Carbon::now();
            $envio->ref_origen = $parameters['ref_origen'];
            $envio->lat_destino = $parameters['lat_destino'];
            $envio->lon_destino = $parameters['lon_destino'];
            $envio->ref_destino = $parameters['ref_destino'];
            $envio->fecha_sug = $parameters['fecha_sug'];
            $envio->hora_sug = $parameters['hora_sug'];
            if ($request->has('max_dias')) {
                $envio->max_dias = $parameters['max_dias'];
            }

            if (is_array($productos)) {
                foreach ($productos as $producto) {
                    $validator = Validator::make($producto, [
                        'producto_id' => 'required|exists:productos,id',
                        'cantidad' => 'integer|min:1',
                        'largo' => 'required|numeric|min:0.01',
                        'ancho' => 'required|numeric|min:0.01',
                        'alto' => 'required|numeric|min:0.01',
                        'peso' => 'required|numeric|min:0.01'
                    ]);

                    if ($validator->fails()) {
                        $messages = $validator->errors();
                        return response()->json(['error' => $messages], 400);
                    }
                }
            }

            //TODO hacer la validacion de los transportes
            if (is_array($transportes)) {
                foreach ($transportes as $transporte) {
                    $validator = Validator::make($transporte, [
                        'id' => 'required|exists:tipo_transporte,id'
                    ]);

                    if ($validator->fails()) {
                        $messages = $validator->errors();
                        return response()->json(['error' => $messages], 400);
                    }
                }
            }


            $nuevoEnvio = $user->envios()->save($envio);

            if (is_array($productos)) {
                foreach ($productos as $productoInfo) {
                    $producto = new Producto();
                    $producto->fill($productoInfo);
                    $producto->producto_id = $productoInfo['producto_id'];

                    $nuevoEnvio->productos()->save($producto);
                }
            }


            if (is_array($transportes)) {
                foreach ($transportes as $transporteInfo) {
                    $transporte = TipoTransporte::find($transporteInfo['id']);
                    $nuevoEnvio->transportes()->save($transporte);
                }
            }

            $nuevoEnvio = Envio::where('id', $nuevoEnvio->id)
                ->with(array('user' => function ($query) {
                    $query->select('id', 'login');
                }))
                ->with('estatus')
                ->with('ofertas')
                ->first();


            return Response::make(json_encode($nuevoEnvio), 201)->header('Location', 'http://acarreos.app/api/v1/cliente/' . $idUsuario . '/envio' . $nuevoEnvio->id)->header('Content-Type', 'application/json');
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($id)
    {
        //Esto se hace en el detalle de envio en EnvioController
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function edit($id)
    {
        //

    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request $request
     * @param  int $idUsuario
     * @return Response
     */
    public function update(Request $request, $idUsuario, $idEnvio)
    {
        //
        $userLogged = $this->getAuthenticatedUser();
        $parameters = array();
        $parameters['user_id'] = $idUsuario;
        $parameters['envio_id'] = $idEnvio;
        $validator = Validator::make($parameters, [
            'envio_id' => 'required|exists:envios,id,user_id,' . $idUsuario,
            'user_id' => 'required|exists:users,id,tipo_user_id,2'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            return response()->json(['error' => $messages], 404);
        }

        $envio = Envio::with('user')->find($idEnvio);
        $valoracionIni = $envio->valoracion;
        if ($valoracionIni == null) {
            $valoracionIni = -1;
        }


        if ($userLogged->isATransportist()) {
            $parameters = array();
            $parameters['user_id'] = $idUsuario;
            $parameters['envio_id'] = $idEnvio;
            $validator = Validator::make($parameters, [
                'envio_id' => 'required|exists:envios,id,user_id,' . $idUsuario,
                'user_id' => 'required|exists:users,id,tipo_user_id,2'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 404);
            }

            $envio = Envio::with('user')->find($idEnvio);

            if ($envio->ganador() != null) {
                $ofertaGanadora = $envio->ganador();
                if ($ofertaGanadora->transportista->id == $userLogged->id) {
                    $validator = Validator::make($request->all(), [
                        'estatus_id' => 'exists:estatus_envio,id'
                    ]);
                    if ($validator->fails()) {
                        $messages = $validator->errors();
                        return response()->json(['error' => $messages], 404);
                    }

                    $estatus = $request->input('estatus_id');
                    if ($estatus == Config::get('constants.ESTATUS_FINALIZADO')) {
                        $envio->estatus_id = $estatus;
                        $envio->save();
                        $notification = new \App\Helpers\PushHandler;
                        $data = ['msg' => 'Su paquete ha llegado al destino',
                            'tipo' => Config::get('constants.NOTIF_ENVIO_FINALIZADO'),
                            'envio' => $envio->toJson()];
                        $notification->generatePush($envio->user, $data);
                        return response()->json(compact('envio'), 200);
                    } else {
                        return response()->json(['error' => 'Unauthorized_User1'], 403);
                    }

                } else {
                    return response()->json(['error' => 'Unauthorized_User2'], 403);
                }
            } else {
                return response()->json(['error' => 'Este envio aun no tiene ganador'], 404);
            }

        }

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $parameters = array();
            $parameters['user_id'] = $idUsuario;
            $parameters['envio_id'] = $idEnvio;
            $validator = Validator::make($parameters, [
                'envio_id' => 'required|exists:envios,id,user_id,' . $idUsuario,
                'user_id' => 'required|exists:users,id,tipo_user_id,2'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 404);
            }

            $validator = Validator::make($request->all(), [
                'short_descripcion' => 'max:50',
                'lat_origen' => 'numeric',
                'lon_origen' => 'numeric',
                'lat_destino' => 'numeric',
                'lon_destino' => 'numeric',
                'fecha_sug' => 'date',
                'max_dias' => 'integer|min:0',
                'fecha_fin' => 'date',
                'valoracion' => 'integer|min:0|max:5',
                'estatus_id' => 'exists:estatus_envio,id'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $user = User::find($idUsuario);
            if (!$user->envios()->where('id', $idEnvio)->first()->update($request->all())) {
                return response()->json(['error' => 'Conflict_Request'], 409);
            }
            $envio = $user->envios()->where('id', $idEnvio)->first();

            $estatus = $request->input('estatus_id');
            if ($estatus == Config::get('constants.ESTATUS_CANCELADO')) {
                $notification = new \App\Helpers\PushHandler;
                $data = ['msg' => 'Su envio ha sido cancelado',
                    'tipo' => Config::get('constants.NOTIF_ENVIO_CANCELADO'),
                    'envio' => Envio::with('user')->find($envio->id)->toJson()];
                $ofertas = $envio->ofertas;

                foreach ($ofertas as $oferta) {
                    $notification->generatePush($oferta->transportista, $data);
                }
                $notification->generatePush($envio->user, $data);
            }

            if ($request->input('valoracion') != $valoracionIni) {
                $notification = new \App\Helpers\PushHandler;
                $data = ['msg' => 'Han valorado sus servicios',
                    'tipo' => Config::get('constants.NOTIF_ENVIO_FINALIZADO'),
                    'envio' => Envio::with('user')->find($envio->id)->toJson()];
                $notification->generatePush($envio->ganador()->transportista, $data);
            }
            return response()->json(compact('envio'), 200);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($id)
    {
        //
    }

    public function getAuthenticatedUser()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }

        // the token is valid and we have found the user via the sub claim
        return $user;
    }

}
