<?php

namespace App\Http\Controllers;

use App\Transporte;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserTransporteController extends Controller
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
    public function index($idUsusario)
    {
        $user = User::find($idUsusario);
        if (!$user) {
            $error = array(
                'error' => 'No se encuentra un usuario con ese codigo'
            );
            return response()->json($error, 404);
        }
        $transportes = $user->transportes()->with('tipoTransporte')->get();
        return response()->json($transportes, 200);
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

        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {
            $parameters = $request->all();
            $parameters['user_id'] = $idUsuario;
            $validator = Validator::make($parameters, [
                'condicion' => 'required',
                'placa' => 'required',
                'poliza_compa' => 'required',
                'poliza_numero' => 'required',
                'transporte_id' => 'required|exists:tipo_transporte,id|unique:user_transporte,transporte_id,NULL,NULL,user_id,' . $idUsuario,
                'user_id' => 'required|exists:users,id,tipo_user_id,3'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $user = User::find($idUsuario);
            $transporte = new Transporte();
            $transporte->condicion = $parameters['condicion'];
            $transporte->placa = $parameters['placa'];
            $transporte->poliza_compa = $parameters['poliza_compa'];
            $transporte->poliza_numero = $parameters['poliza_numero'];
            $transporte->transporte_id = $parameters['transporte_id'];
            if ($request->file('photo') != null) {
                $imageName = 'user_' . $userLogged->id .
                    '_trans_' . $parameters['transporte_id'] . '.' .
                    $request->file('photo')->getClientOriginalExtension();
                $request->file('photo')->move(
                    base_path() . '/public/imagenes/transportes/', $imageName
                );
                $transporte->foto = 'http://acarreospanama.info/imagenes/transportes/' . $imageName;
            }
            $nuevoTransporte = $user->transportes()->save($transporte);
            $nuevoTransporte = $user->transportes()->where('transporte_id', $transporte->transporte_id)->with('tipoTransporte')->first();

            return Response::make(json_encode($nuevoTransporte), 201)->header('Location', 'http://acarreos.app/api/v1/transportista/' . $idUsuario . '/transporte' . $nuevoTransporte->id)->header('Content-Type', 'application/json');
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $idUsuario
     * @return Response
     */
    public function show($idUsuario, $idTipoTransporte)
    {
        // muestra el transporte de un usuario
        $parameters = array();
        $parameters['user_id'] = $idUsuario;
        $parameters['transporte_id'] = $idTipoTransporte;
        $validator = Validator::make($parameters, [
            'transporte_id' => 'required|exists:user_transporte,transporte_id,user_id,' . $idUsuario,
            'user_id' => 'required|exists:users,id,tipo_user_id,3'
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            return response()->json(['error' => $messages], 404);
        }
        $user = User::find($idUsuario);
        $transporte = $user->transportes()->with('tipoTransporte')->where('transporte_id', $idTipoTransporte)->first();

        return response()->json($transporte, 200);
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
    public function update(Request $request, $idUsuario, $idTipoTransporte)
    {

        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $parameters = array();
            $parameters['user_id'] = $idUsuario;
            $parameters['transporte_id'] = $idTipoTransporte;
            $validator = Validator::make($parameters, [
                'transporte_id' => 'required|exists:user_transporte,transporte_id,user_id,' . $idUsuario,
                'user_id' => 'required|exists:users,id,tipo_user_id,3'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 404);
            }

            $validator = Validator::make($request->all(), [
                'condicion' => 'required|max:255',
                'foto' => 'file',
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $user = User::find($idUsuario);
            if (!$user->transportes()->where('transporte_id', $idTipoTransporte)->first()->update($request->all())) {
                return response()->json(['error' => 'Conflict_Request'], 409);
            }
            $transporte = $user->transportes()->where('transporte_id', $idTipoTransporte)->first();
            return response()->json(compact('transporte'), 200);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $idUsuario
     * @return Response
     */
    public function destroy($idUsuario, $idTipoTransporte)
    {
        //
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {
            $parameters = array();
            $parameters['user_id'] = $idUsuario;
            $parameters['transporte_id'] = $idTipoTransporte;
            $validator = Validator::make($parameters, [
                'transporte_id' => 'required|exists:user_transporte,transporte_id,user_id,' . $idUsuario,
                'user_id' => 'required|exists:users,id,tipo_user_id,3'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 404);
            }

            $user = User::find($idUsuario);
            if (!$user->transportes()->where('transporte_id', $idTipoTransporte)->delete()) {
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
