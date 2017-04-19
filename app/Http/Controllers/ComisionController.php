<?php

namespace App\Http\Controllers;

use App\Comision;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class ComisionController extends Controller
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
    public function index(Request $request)
    {
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin()) {
            $comisiones = Comision::with('tipoProducto')->get();
            return response()->json($comisiones, 200);
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
    public function store(Request $request)
    {
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->isAnAdmin()) {

            $validator = Validator::make($request->all(), [
                'producto_id' => 'required|exists:productos,id|unique:comisiones,producto_id',
                'comision' => 'required|integer|min:0|max:100'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $comision = new Comision($request->all());
            $comision->save();
            return response()->json($comision, 200);
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
    public function update(Request $request, $idComision)
    {
        $userLogged = $this->getAuthenticatedUser();

        $comision = Comision::find($idComision);

        if (!$comision) {
            $error = array(
                'error' => 'No se encuentra una comision con ese codigo'
            );
            return response()->json($error, 404);
        }

        if ($userLogged->isAnAdmin()) {

            $validator = Validator::make($request->all(), [
                'comision' => 'required|integer'
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $comision = Comision::find($idComision);
            $comision->comision = $request->input('comision');
            $comision->save();
            return response()->json($comision, 200);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy($idComision)
    {
        $userLogged = $this->getAuthenticatedUser();

        $comision = Comision::find($idComision);

        if (!$comision) {
            $error = array(
                'error' => 'No se encuentra una comision con ese codigo'
            );
            return response()->json($error, 404);
        }

        if ($userLogged->isAnAdmin()) {

            $comision->delete();
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
