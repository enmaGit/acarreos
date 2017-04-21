<?php

namespace App\Http\Controllers;

use App\Oferta;
use App\User;
use Illuminate\Http\Request;

use App\Http\Requests;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Facades\JWTAuth;

class UserController extends Controller
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

        if ($request->has('tipo_user_id')) {
            if ($request->input('tipo_user_id') == Config::get('constants.TIPO_ADMIN')) {
                if (!$userLogged->isAnAdmin()) {
                    return response()->json(['error' => 'Unauthorized_User'], 403);
                }
            }
            $users = User::where('tipo_user_id', $request->input('tipo_user_id'))->simplePaginate(10);
        } else {
            if (!$userLogged->isAnAdmin()) {
                return response()->json(['error' => 'Unauthorized_User'], 403);
            }
            $users = User::simplePaginate(10);
        }
        return response()->json($users->items());

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
    public function show($id)
    {
        $userLogged = $this->getAuthenticatedUser();
        $user = User::find($id);
        if (!$user) {
            $error = array(
                'error' => 'No se encuentra un usuario con ese codigo'
            );
            return response()->json($error, 404);
        }
        if ($user->isAnAdmin()) {
            if (!$userLogged->isAnAdmin()) {
                return response()->json(['error' => 'Unauthorized_User'], 403);
            }
        }
        return response()->json($user, 200);
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
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validator(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'max:255',
            'apellido' => 'max:255',
            'email' => 'email|max:255|unique:users,email,' . $data['id'],
            'tipo_dni' => 'max:255',
            'dni' => 'max:255|unique:users,dni,' . $data['id'],
            'tipo_licencia' => 'max:255',
            'num_seguridad' => 'max:255|unique:users,num_seguridad,' . $data['id'],
            'password' => 'min:6',
        ]);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected function validatorPut(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users,email,' . $data['id'],
            'tipo_dni' => 'required|max:255',
            'dni' => 'required|max:255|unique:users,dni,' . $data['id'],
            'tipo_licencia' => 'required|max:255',
            'num_seguridad' => 'required|max:255|unique:users,num_seguridad,' . $data['id'],
            'password' => 'required|min:6',
            'telefono' => 'required',
            'fecha_nac' => 'required',
            'estatus' => 'required',
        ]);
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
        $userLogged = $this->getAuthenticatedUser();

        if ($userLogged->id == $id || $userLogged->isAnAdmin()) {

            $user = User::find($id);

            if (!$user) {
                return response()->json(['error' => 'Resource_Not_Found'], 404);
            }


            if ($request->method() == Config::get('constants.METHOD_PATCH')) {
                $validator = $this->validator($request->all());

                if ($validator->fails()) {
                    $messages = $validator->errors();
                    return response()->json(['error' => $messages], 400);
                }

                $nombre = $request->input('nombre');
                $apellido = $request->input('apellido');
                $password = $request->input('password');
                $telefono = $request->input('telefono');
                $email = $request->input('email');
                $fecha_nac = $request->input('fecha_nac');
                $estatus = $request->input('estatus');
                $id_push = $request->input('id_push');
                $tipo_dni = $request->input('tipo_dni');
                $dni = $request->input('dni');
                $tipo_licencia = $request->input('tipo_licencia');
                $num_seguridad = $request->input('num_seguridad');


                $modificado = false;

                if ($nombre) {
                    $user->nombre = $nombre;
                    $modificado = true;
                }

                if ($apellido) {
                    $user->apellido = $apellido;
                    $modificado = true;
                }

                if ($password) {
                    $user->password = Hash::make($password);
                    $modificado = true;
                }

                if ($telefono) {
                    $user->telefono = $telefono;
                    $modificado = true;
                }

                if ($email) {
                    $user->email = $email;
                    $modificado = true;
                }

                if ($fecha_nac) {
                    $user->fecha_nac = $fecha_nac;
                    $modificado = true;
                }

                if ($id_push) {
                    $user->id_push = $id_push;
                    $modificado = true;
                }

                if ($tipo_dni) {
                    $user->tipo_dni = $tipo_dni;
                    $modificado = true;
                }

                if ($dni) {
                    $user->dni = $dni;
                    $modificado = true;
                }

                if ($tipo_licencia) {
                    $user->tipo_licencia = $tipo_licencia;
                    $modificado = true;
                }

                if ($num_seguridad) {
                    $user->num_seguridad = $num_seguridad;
                    $modificado = true;
                }

                if ($estatus) {
                    if ($userLogged->isAnAdmin()) {
                        $user->estatus = $estatus;
                        $modificado = true;
                    } else {
                        return response()->json(['error' => 'Unauthorized_User'], 403);
                    }
                }

                if (!$modificado) {
                    return response()->json(['error' => 'Not_Modified'], 400);
                }

                if (!$user->save()) {
                    return response()->json(['error' => 'Conflict_Request'], 409);
                }
                return response()->json(compact('user'), 200);
            } else {
                if ($userLogged->isAnAdmin()) {
                    $validator = $this->validatorPut($request->all());

                    if ($validator->fails()) {
                        $messages = $validator->errors();
                        return response()->json(['errors' => $messages], 422);
                    }
                    $password = $request->input('password');
                    $user->update($request->all());
                    $user->estatus = $request->get('estatus');
                    $user->password = Hash::make($password);
                    if (!$user->save()) {
                        return response()->json(['error' => 'Conflict_Request'], 409);
                    }
                    return response()->json(compact('user'), 200);
                } else {
                    return response()->json(['error' => 'Unauthorized_User'], 403);
                }
            }
        }
        return response()->json(['error' => 'Unauthorized_User'], 403);
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
