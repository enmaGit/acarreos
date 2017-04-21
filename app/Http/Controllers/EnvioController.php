<?php

namespace App\Http\Controllers;

use App\Envio;
use App\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnvioController extends Controller
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
        //
        $envios = Envio::orderBy('fecha_pub', 'desc')->with('estatus')->with('ofertas')->simplePaginate(10);
        $user = $this->getAuthenticatedUser();
        if ($user->isATransportist()) {
            if ($request->has('estatus')) {
                $estatus = $request->input('estatus');
                $listaDeEstatus = explode(',', $estatus);
                if ($request->has('ofertado')) {
                    if ($request->input('ofertado') == 1) {
                        $envios = Envio::whereHas('ofertas', function ($query) use ($user) {
                            $query->where('transpor_id', $user->id);
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
                $envios = Envio::whereHas('transportes', function ($query) use ($user) {
                    $transportes = DB::table('user_transporte')
                        ->where('user_id', $user->id)
                        ->lists('transporte_id');
                    $query->whereIn('transporte_id', $transportes);
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
        if ($request->has('estatus_id')) {
            if ($request->has('fecha_res')) {
                $envios = Envio::orderBy('fecha_pub', 'desc')
                    ->with('estatus')
                    ->where('estatus_id', $request->input('estatus_id'))
                    ->where('fecha_res', $request->input('fecha_res'))
                    ->simplePaginate(10);
            } else {
                $envios = Envio::orderBy('fecha_pub', 'desc')
                    ->with('estatus')
                    ->where('estatus_id', $request->input('estatus_id'))
                    ->simplePaginate(10);
            }
        } elseif ($request->has('fecha_res')) {
            $envios = Envio::orderBy('fecha_pub', 'desc')
                ->with('estatus')
                ->where('estatus_id', $request->input('estatus_id'))
                ->where('fecha_res', $request->input('fecha_res'))
                ->simplePaginate(10);
        }
        return response()->json($envios->items(), 200);
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return Response
     */
    public
    function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request $request
     * @return Response
     */
    public
    function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public
    function show($idEnvio)
    {
        //
        $envio = Envio::with('estatus')->with(array('user' => function ($query) {
            $query->select('id', 'login');
        }))
            ->with('estatus')
            ->with('ofertas')->find($idEnvio);
        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }
        return response()->json($envio, 200);
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public
    function edit($id)
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
    public
    function update(Request $request, $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public
    function destroy($id)
    {
        //
    }

    public
    function getAuthenticatedUser()
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
