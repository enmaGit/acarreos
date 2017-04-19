<?php

namespace App\Http\Controllers;

use App\TipoProducto;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class TipoProductoController extends Controller
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
    public function index()
    {
        //
        $tipoProductos = TipoProducto::get();
        return response()->json($tipoProductos, 200);
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
    public function store(Request $request)
    {
        //
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin()) {

            $validator = Validator::make($request->all(), [
                'nombre' => 'required|string|max:120',
                'descripcion' => 'required|string',
                'comision' => 'required|integer',
                'dias_puja' => 'required|integer',
            ]);
            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }
            $tipoProducto = TipoProducto::create($request->all());
            $tipoProducto->save();
            return Response::make(json_encode($tipoProducto), 201)->header('Location', 'http://acarreos.app/api/v1/tipo_producto/' . $tipoProducto->id)->header('Content-Type', 'application/json');
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($idTipoProducto)
    {
        $tipoProducto = TipoProducto::find($idTipoProducto);
        if (!$tipoProducto) {
            $error = array(
                'error' => 'No se encuentra un tipo producto con ese codigo'
            );
            return response()->json($error, 404);
        }
        return response()->json($tipoProducto, 200);
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
    public function update(Request $request, $idTipoProducto)
    {
        //
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin()) {

            $tipoProducto = TipoProducto::find($idTipoProducto);
            if (!$tipoProducto) {
                $error = array(
                    'error' => 'No se encuentra un tipo producto con ese codigo'
                );
                return response()->json($error, 404);
            }

            $validator = Validator::make($request->all(), [
                'nombre' => 'string|max:120',
                'descripcion' => 'string',
                'comision' => 'integer',
                'dias_puja' => 'integer',
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $modificado = false;

            if ($request->has('nombre')) {
                $tipoProducto->nombre = $request->input('nombre');
                $modificado = true;
            }

            if ($request->has('descripcion')) {
                $tipoProducto->descripcion = $request->input('descripcion');
                $modificado = true;
            }

            if ($request->has('comision')) {
                $tipoProducto->comision = $request->input('comision');
                $modificado = true;
            }

            if ($request->has('dias_puja')) {
                $tipoProducto->dias_puja = $request->input('dias_puja');
                $modificado = true;
            }

            if (!$modificado) {
                return response()->json(['error' => 'Not_Modified'], 400);
            }

            $tipoProducto->save();

            return response()->json($tipoProducto, 200);

        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($idTipoProducto)
    {
        //
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin()) {

            $tipoProducto = TipoProducto::find($idTipoProducto);
            if (!$tipoProducto) {
                $error = array(
                    'error' => 'No se encuentra un tipo producto con ese codigo'
                );
                return response()->json($error, 404);
            }

            $tipoProducto->delete();
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
