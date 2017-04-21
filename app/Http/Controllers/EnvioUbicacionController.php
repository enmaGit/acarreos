<?php

namespace App\Http\Controllers;

use App\Envio;
use App\Ubicacion;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnvioUbicacionController extends Controller
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
    public function index($idEnvio)
    {
        //TODO revisar esta loquera
        $userLogged = $this->getAuthenticatedUser();
        $envio = Envio::find($idEnvio);

        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }

        if (!$envio->ganador()) {
            /*$error = array(
                'error' => 'Envio no valido'
            );
            return response()->json($error, 403);*/
            return response()->json(array(), 200);
        }

        $idCliente = $envio->user->id;
        $idTransportista = $envio->ganador()->transportista->id;

        if ($userLogged->isAnAdmin() || $userLogged->id == $idCliente || $userLogged->id == $idTransportista) {
            return response()->json($envio->ubicaciones, 200);
        }


        return response()->json(['error' => 'Unauthorized_User'], 403);
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
    public function store(Request $request, $idEnvio)
    {
        $userLogged = $this->getAuthenticatedUser();
        $envio = Envio::find($idEnvio);

        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }

        if (!$envio->ganador()) {
            $error = array(
                'error' => 'Envio no valido'
            );
            return response()->json($error, 404);
        }

        $idTransportista = $envio->ganador()->transportista->id;

        if ($userLogged->isAnAdmin() || $userLogged->id == $idTransportista) {

            $validator = Validator::make($request->all(), [
                'latitud' => 'required|numeric',
                'longitud' => 'required|numeric'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $ubicacion = new Ubicacion();
            $ubicacion->fill($request->all());
            $ubicacion->fecha_update = Carbon::now();
            $ubicacion = $envio->ubicaciones()->save($ubicacion);
            $notification = new \App\Helpers\PushHandler;
            $data = ['msg' => 'Han actualizado la ubicacion',
                'tipo' => Config::get('constants.NOTIF_ACT_UBICACION'),
                'envio' => Envio::with(array('user' => function ($query) {
                    $query->select('id', 'login');
                }))
                    ->with('estatus')->find($envio->id)->toJson()];
            $notification->generatePush($envio->user, $data);

            return Response::make(Ubicacion::find($ubicacion->id)->toJson(), 201)->header('Location', 'http://acarreos.app/api/v1/envio/' . $idEnvio . '/ubicacion')->header('Content-Type', 'application/json');
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
        //
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
     * @param  int $id
     * @return Response
     */
    public function update(Request $request, $idEnvio, $idUbicacion)
    {
        $userLogged = $this->getAuthenticatedUser();
        $envio = Envio::find($idEnvio);

        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }

        if (!$envio->ganador()) {
            $error = array(
                'error' => 'Envio no valido'
            );
            return response()->json($error, 404);
        }

        $idTransportista = $envio->ganador()->transportista->id;

        if ($userLogged->isAnAdmin() || $userLogged->id == $idTransportista) {

            $parameters = $request->all();
            $parameters['ubicacion_id'] = $idUbicacion;
            $validator = Validator::make($request->all(), [
                'ubicacion_id' => 'exists:ubicacion_envio,id,envio_id,' . $idEnvio,
                'latitud' => 'numeric',
                'longitud' => 'numeric'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $modificado = false;

            $ubicacion = Ubicacion::find($idUbicacion);

            if ($request->has('latitud')) {
                $ubicacion->latitud = $request->input('latitud');
                $modificado = true;
            }

            if ($request->has('longitud')) {
                $ubicacion->longitud = $request->input('longitud');
                $modificado = true;
            }

            if (!$modificado) {
                return response()->json(['error' => 'Not_Modified'], 400);
            }
            $ubicacion->fecha_update = Carbon::now();
            $ubicacion->save();
            return response()->json(compact('ubicacion'), 200);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $idEnvio, $idUbicacion)
    {
        $userLogged = $this->getAuthenticatedUser();
        $envio = Envio::find($idEnvio);

        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }

        if (!$envio->ganador()) {
            $error = array(
                'error' => 'Envio no valido'
            );
            return response()->json($error, 404);
        }

        $idTransportista = $envio->ganador()->transportista->id;

        if ($userLogged->isAnAdmin() || $userLogged->id == $idTransportista) {

            $parameters = $request->all();
            $parameters['ubicacion_id'] = $idUbicacion;
            $validator = Validator::make($request->all(), [
                'ubicacion_id' => 'exists:ubicacion_envio,id,envio_id,' . $idEnvio
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $ubicacion = Ubicacion::find($idUbicacion);

            $ubicacion->delete();

            return response()->json(['mensaje' => 'Borrado exitoso'], 204);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
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
