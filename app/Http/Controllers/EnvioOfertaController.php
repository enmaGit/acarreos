<?php

namespace App\Http\Controllers;

use App\Envio;
use App\Oferta;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnvioOfertaController extends Controller
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
    public function index(Request $request, $idEnvio)
    {
        //
        $envio = Envio::find($idEnvio);
        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }
        if ($request->has('sort')) {
            $campoSort = $request->input('sort');
            if (!Schema::hasColumn('ofertas', $campoSort)) {
                return response()->json(['error' => 'Columna invalida para ordenar'], 400);
            }
            $ofertas = $envio->ofertas()->orderBy($campoSort, 'desc')->with('transportista')->simplePaginate(10);
        } else {
            $ofertas = $envio->ofertas()->orderBy('fecha_puja', 'desc')->with('transportista')->simplePaginate(10);
        }
        return response()->json($ofertas->items(), 200);
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
        //
        $userLogged = $this->getAuthenticatedUser();
        $envio = Envio::find($idEnvio);

        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }

        if ($envio->estatus_id != Config::get('constants.ESTATUS_SUBASTA')) {
            $error = array(
                'error' => 'Codigo de envio invalido'
            );
            return response()->json($error, 400);
        }

        if ($userLogged->isAnAdmin() || ($userLogged->isATransportist())) {

            $idUsuario = $userLogged->id;
            $parameters = $request->all();
            $parameters['transpor_id'] = $idUsuario;
            if ($userLogged->isAnAdmin()) {
                $parameters['transpor_id'] = $request->input('transpor_id');
            }

            if ($userLogged->isATransportist()) {
                if (!$userLogged->puedeOfertar($idEnvio)) {
                    return response()->json(['error' => 'No puede ofertar a este envio'], 400);
                }
            }

            $rules = array(
                'hora_salida' => array('required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'),
                'hora_llegada' => array('required', 'regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'),
                'transpor_id' => 'required|unique:ofertas,transpor_id,NULL,NULL,envio_id,' . $idEnvio,
                'precio_puja' => 'required|numeric|min:0',
                'fecha_salida' => 'required|date',
                'fecha_llegada' => 'required|date',
            );

            $validator = Validator::make($parameters, $rules);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $oferta = new Oferta();
            $oferta->fill($request->all());
            $oferta->transpor_id = $idUsuario;
            if ($userLogged->isAnAdmin()) {
                $oferta->transpor_id = $request->input('transpor_id');
            }
            $envio->ofertas()->save($oferta);

            return Response::make(json_encode($oferta), 201)->header('Location', 'http://acarreos.app/api/v1/envio/' . $idEnvio . '/oferta' . $oferta->id)->header('Content-Type', 'application/json');

        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($idEnvio, $idOferta)
    {
        $parameters = array();
        $parameters['envio_id'] = $idEnvio;
        $parameters['oferta_id'] = $idOferta;
        $validator = Validator::make($parameters, [
            'envio_id' => 'required|exists:envios,id',
            'oferta_id' => 'required|exists:ofertas,id,envio_id,' . $idEnvio
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            return response()->json(['error' => $messages], 404);
        }

        $oferta = Oferta::with('transportista')->find($idOferta);

        return response()->json($oferta, 200);
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
    public function update(Request $request, $idEnvio, $idOferta)
    {
        $userLogged = $this->getAuthenticatedUser();

        $envio = Envio::find($idEnvio);

        if ($envio->estatus_id != Config::get('constants.ESTATUS_SUBASTA')) {
            $error = array(
                'error' => 'Codigo de envio invalido'
            );
            return response()->json($error, 400);
        }

        $parameters = array();
        $parameters['envio_id'] = $idEnvio;
        $parameters['oferta_id'] = $idOferta;
        $validator = Validator::make($parameters, [
            'envio_id' => 'required|exists:envios,id',
            'oferta_id' => 'required|exists:ofertas,id,envio_id,' . $idEnvio
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            return response()->json(['error' => $messages], 404);
        }

        $oferta = Oferta::find($idOferta);

        $idUsuario = $oferta->transportista->id;

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $rules = array(
                'hora_salida' => array('regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'),
                'hora_llegada' => array('regex:/^([01]?[0-9]|2[0-3]):[0-5][0-9]:[0-5][0-9]$/'),
                'precio_puja' => 'numeric|min:0',
                'fecha_salida' => 'date',
                'fecha_llegada' => 'date'
            );

            $validator = Validator::make($parameters, $rules);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $modificado = false;

            if ($request->has('hora_salida')) {
                $oferta->hora_salida = $request->input('hora_salida');
                $modificado = true;
            }

            if ($request->has('hora_llegada')) {
                $oferta->hora_llegada = $request->input('hora_llegada');
                $modificado = true;
            }

            if ($request->has('precio_puja')) {
                $oferta->precio_puja = $request->input('precio_puja');
                $modificado = true;
            }

            if ($request->has('fecha_salida')) {
                $oferta->fecha_salida = $request->input('fecha_salida');
                $modificado = true;
            }

            if ($request->has('fecha_llegada')) {
                $oferta->fecha_llegada = $request->input('fecha_llegada');
                $modificado = true;
            }

            if (!$modificado) {
                return response()->json(['error' => 'Not_Modified'], 400);
            }

            $oferta->save();

            return response()->json($oferta, 200);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($idEnvio, $idOferta)
    {
        $userLogged = $this->getAuthenticatedUser();

        $envio = Envio::find($idEnvio);

        if ($envio->estatus_id != Config::get('constants.ESTATUS_SUBASTA')) {
            $error = array(
                'error' => 'Codigo de envio invalido'
            );
            return response()->json($error, 400);
        }

        $parameters = array();
        $parameters['envio_id'] = $idEnvio;
        $parameters['oferta_id'] = $idOferta;
        $validator = Validator::make($parameters, [
            'envio_id' => 'required|exists:envios,id',
            'oferta_id' => 'required|exists:ofertas,id,envio_id,' . $idEnvio
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            return response()->json(['error' => $messages], 404);
        }

        $oferta = Oferta::find($idOferta);

        $idUsuario = $oferta->transportista->id;

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            if (!$oferta->delete()) {
                return response()->json(['error' => 'Conflict_Request'], 409);
            }
            return response()->json(compact(['mensaje' => 'Borrado exitoso']), 204);

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
