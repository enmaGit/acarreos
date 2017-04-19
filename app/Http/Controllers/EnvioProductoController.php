<?php

namespace App\Http\Controllers;

use App\Envio;
use App\Producto;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnvioProductoController extends Controller
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
        //
        $envio = Envio::find($idEnvio);
        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }
        $productos = $envio->productos()->with('tipoProducto')->get();
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

        if ($envio->estatus_id != Config::get('constants.ESTATUS_SUBASTA')) {
            $error = array(
                'error' => 'Codigo de envio invalido'
            );
            return response()->json($error, 400);
        }

        $idUsuario = $envio->user->id;
        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $validator = Validator::make($request->all(), [
                'producto_id' => 'required|exists:productos,id|unique:productos_envio,producto_id,NULL,NULL,envio_id,' . $idEnvio,
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

            $producto = new Producto();
            $producto->fill($request->all());
            $producto->producto_id = $request->input('producto_id');

            $producto = $envio->productos()->save($producto);

            $producto = Producto::where('envio_id',$idEnvio)
                                        ->where('producto_id',$producto->producto_id)
                                        ->with('tipoProducto')->first();

            return Response::make(json_encode($producto), 201)->header('Location', 'http://acarreos.app/api/v1/envio/' . $idEnvio . '/producto' . $producto->producto_id)->header('Content-Type', 'application/json');
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Display the specified resource.
     *
     * @param  int $idEnvio
     * @return Response
     */
    public function show($idEnvio, $idProducto)
    {
        //
        $parameters = array();
        $parameters['envio_id'] = $idEnvio;
        $parameters['producto_id'] = $idProducto;
        $validator = Validator::make($parameters, [
            'envio_id' => 'required|exists:envios,id',
            'producto_id' => 'required|exists:productos_envio,producto_id,envio_id,' . $idEnvio
        ]);

        if ($validator->fails()) {
            $messages = $validator->errors();
            return response()->json(['error' => $messages], 404);
        }

        $envio = Envio::find($idEnvio);
        $producto = $envio->productos()->with('tipoProducto')->where('producto_id', $idProducto)->first();

        return response()->json($producto, 200);
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
    public function update(Request $request, $idEnvio, $idProducto)
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

        $idUsuario = $envio->user->id;

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $parameters = array();
            $parameters['producto_id'] = $idProducto;
            $validator = Validator::make($parameters, [
                'producto_id' => 'required|exists:productos_envio,producto_id,envio_id,' . $idEnvio,
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 404);
            }

            $validator = Validator::make($request->all(), [
                'cantidad' => 'integer|min:1',
                'largo' => 'numeric|min:0.01',
                'ancho' => 'numeric|min:0.01',
                'alto' => 'numeric|min:0.01',
                'peso' => 'numeric|min:0.01'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $envio = Envio::find($idEnvio);
            if (!$envio->productos()->where('producto_id', $idProducto)->first()->update($request->all())) {
                return response()->json(['error' => 'Conflict_Request'], 409);
            }
            $producto = $envio->productos()->where('producto_id', $idProducto)->first();
            return response()->json(compact('producto'), 200);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($idEnvio, $idProducto)
    {
        //
        $userLogged = $this->getAuthenticatedUser();
        $envio = Envio::find($idEnvio);

        if ($envio->estatus_id != Config::get('constants.ESTATUS_SUBASTA')) {
            $error = array(
                'error' => 'Codigo de envio invalido'
            );
            return response()->json($error, 400);
        }

        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }

        $idUsuario = $envio->user->id;

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {
            $parameters = array();
            $parameters['producto_id'] = $idProducto;
            $validator = Validator::make($parameters, [
                'producto_id' => 'required|exists:productos_envio,producto_id,envio_id,' . $idEnvio
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 404);
            }

            $envio = Envio::find($idEnvio);
            if (!$envio->productos()->where('producto_id', $idProducto)->delete()) {
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
