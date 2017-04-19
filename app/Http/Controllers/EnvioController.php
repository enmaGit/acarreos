<?php

namespace App\Http\Controllers;

use App\Envio;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;

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
        $envios = Envio::orderBy('fecha_pub', 'desc')->with('estatus')->simplePaginate(10);
        if ($request->has('estatus_id')) {
            $envios = Envio::orderBy('fecha_pub', 'desc')->with('estatus')->where('estatus_id', $request->input('estatus_id'))->simplePaginate(10);;
        }
        return response()->json($envios->items(), 200);
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
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return Response
     */
    public function show($idEnvio)
    {
        //
        $envio = Envio::with('estatus')->find($idEnvio);
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
    public function destroy($id)
    {
        //
    }
}
