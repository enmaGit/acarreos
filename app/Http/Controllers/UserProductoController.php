<?php

namespace App\Http\Controllers;

use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserProductoController extends Controller
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
    public function index($idUsuario)
    {
        //
        $user = User::find($idUsuario);
        if (!$user) {
            $error = array(
                'error' => 'No se encuentra un usuario con ese codigo'
            );
            return response()->json($error, 404);
        }
        $productos = $user->productos()->get();
        return response()->json($productos, 200);
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
     * @param $idUsuario
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
                'producto_id' => 'required|exists:productos,id|unique:carrier_producto,producto_id,NULL,NULL,transpor_id,' . $idUsuario,
                'user_id' => 'required|exists:users,id,tipo_user_id,3'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $user = User::find($idUsuario);
            $user->productos()->attach($request->input('producto_id'));

            return response()->json(['mensaje' => 'Guardado exitoso'], 200);
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
    public function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($idUsuario, $idTipoProducto)
    {
        //
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {
            $parameters = array();
            $parameters['user_id'] = $idUsuario;
            $parameters['producto_id'] = $idTipoProducto;
            $validator = Validator::make($parameters, [
                'producto_id' => 'required|exists:carrier_producto,producto_id,transpor_id,' . $idUsuario,
                'user_id' => 'required|exists:users,id,tipo_user_id,3'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $user = User::find($idUsuario);
            $user->productos()->detach($idTipoProducto);

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
