<?php

namespace App\Http\Controllers;

use App\Envio;
use App\Oferta;
use Carbon\Carbon;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class EnvioGanadorController extends Controller
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
        $envio = Envio::find($idEnvio);
        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }
        $ganador = $envio->ganador();
        return response()->json($ganador, 200);
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

        $stripeToken = $request->all();

        if ($envio->ofertas()->where('ganador', 1)->count() > 0) {
            $error = array(
                'error' => 'Este envio ya tiene ganador'
            );
            return response()->json($error, 400);
        }

        $idUsuario = $envio->user->id;

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $validator = Validator::make($request->all(), [
                'oferta_id' => 'required|exists:ofertas,id,envio_id,' . $idEnvio,
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 404);
            }

            $oferta = Oferta::find($request->input('oferta_id'));

            $amount = $oferta->precio_puja * ($envio->comision_final / 100);

            try {
              $paymentStatus = \App\Helpers\StripeHelper::generateCharge($stripeToken['mId'], $amount, 'Charge for shipment with id: ' . $envio->id);
            } catch (Exception $e) {
              $error = array(
                  'error' => 'No se pudo procesar el pago'
              );
              return response()->json($error, 400);
            }


            $oferta->ganador = true;
            $envio->fecha_res = Carbon::now();
            $envio->estatus_id = Config::get('constants.ESTATUS_DESARROLLO');
            $oferta->save();
            $envio->save();
            $notification = new \App\Helpers\PushHandler;
            $data = ['msg' => 'Su oferta ha sido aceptada',
                'tipo' => Config::get('constants.NOTIF_OFERTA_ACEPTADA'),
                'envio' => Envio::with(array('user' => function ($query) {
                    $query->select('id', 'login');
                }))
                    ->with('estatus')->with('ofertas')->find($envio->id)->toJson()];
            $ofertas = $envio->ofertas;

            foreach ($ofertas as $oferta) {
                if ($oferta->transportista->id != $userLogged->id)
                    $notification->generatePush($oferta->transportista, $data);
            }
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
    public function update(Request $request, $idEnvio, $idOfertaGanadora)
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

        if ($envio->fecha_res == null) {
            $error = array(
                'error' => 'Este env�o aun no tiene ganador'
            );
            return response()->json($error, 404);
        }

        if ($envio->ganador()->id != $idOfertaGanadora) {
            $error = array(
                'error' => 'El id del ganador es incorrecto'
            );
            return response()->json($error, 400);
        }

        $idUsuario = $envio->user->id;

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $validator = Validator::make($request->all(), [
                'oferta_id' => 'required|exists:ofertas,id,envio_id,' . $idEnvio,
            ]);

            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            $oferta = Oferta::find($request->input('oferta_id'));
            $oferta->ganador = true;
            $envio->fecha_res = Carbon::now();
            $oferta->save();
            $envio->save();
            return response()->json(['mensaje' => 'Actualizado exitoso'], 200);
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return Response
     */
    public function destroy(Request $request, $idEnvio, $idOfertaGanadora)
    {
        $userLogged = $this->getAuthenticatedUser();
        $envio = Envio::find($idEnvio);

        if (!$envio) {
            $error = array(
                'error' => 'No se encuentra un envio con ese codigo'
            );
            return response()->json($error, 404);
        }

        if ($envio->fecha_res == null) {
            $error = array(
                'error' => 'Este env�o aun no tiene ganador'
            );
            return response()->json($error, 404);
        }

        if ($envio->ganador()->id != $idOfertaGanadora) {
            $error = array(
                'error' => 'El id del ganador es incorrecto'
            );
            return response()->json($error, 400);
        }

        $idUsuario = $envio->user->id;

        if ($userLogged->isAnAdmin() || ($userLogged->id == $idUsuario)) {

            $oferta = Oferta::find($idOfertaGanadora);
            $oferta->ganador = false;
            $envio->fecha_res = null;
            $oferta->save();
            $envio->save();
            return response()->json(['mensaje' => 'Borrado exitoso'], 200);
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
