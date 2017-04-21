<?php

namespace App\Http\Controllers\Auth;

use App\User;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Validator;
use Tymon\JWTAuth\Exceptions\JWTException;
use Tymon\JWTAuth\Facades\JWTAuth;
use Illuminate\Support\Facades\Config;

class AuthController extends Controller
{
    /*
    |--------------------------------------------------------------------------
    | Registration & Login Controller
    |--------------------------------------------------------------------------
    |
    | This controller handles the registration of new users, as well as the
    | authentication of existing users. By default, this controller uses
    | a simple trait to add these behaviors. Why don't you explore it?
    |
    */

    /**
     * Create a new authentication controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('guest', ['except' => ['getLogout', 'postRegister']]);
    }

    /**
     * Handle a login request to the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public function postLogin(Request $request)
    {
        // grab credentials from the request
        $credentials = $request->only('login', 'password');

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }

        // all good so return the token
        $userLogged = User::where('login', $request->input('login'))->get()->first();
        if ($request->has('tipo_user_id')) {
            if ($userLogged->tipo_user_id == $request->input('tipo_user_id')) {
                if ($request->input("id_push") != null) {
                    $userLogged->id_push = $request->input("id_push");
                    $userLogged->save();
                    $notification = new \App\Helpers\PushHandler;
                    $notification->checkNotif($userLogged);
                }
                //$notification = new \App\Helpers\PushHandler;
                //$notification->generatePush($userLogged->id_push, ['msg' => 'hola mundo']);
                return response()->json(array('user' => User::find($userLogged->id), 'token' => $token));
                //return response()->json(compact('token'));
            } else {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } else {
            if ($request->input("id_push") != null) {
                $userLogged->id_push = $request->input("id_push");
                $userLogged->save();
                $notification = new \App\Helpers\PushHandler;
                $notification->checkNotif($userLogged);
            }
            //$notification = new \App\Helpers\PushHandler;
            //$notification->generatePush($userLogged->id_push, ['msg' => 'hola mundo']);
            return response()->json(array('user' => User::find($userLogged->id), 'token' => $token));
        }
    }

    /**
     * Log the user out of the application.
     *
     * @return \Illuminate\Http\Response
     */
    public
    function getLogout()
    {
        try {

            if (!$user = JWTAuth::parseToken()->authenticate()) {
                return response()->json(['user_not_found'], 404);
            }

            $user->id_push = "";
            $user->save();

            JWTAuth::parseToken();
            $token = JWTAuth::getToken();
            JWTAuth::invalidate($token);

        } catch (\Tymon\JWTAuth\Exceptions\TokenExpiredException $e) {

            return response()->json(['token_expired'], $e->getStatusCode());

        } catch (\Tymon\JWTAuth\Exceptions\TokenInvalidException $e) {

            return response()->json(['token_invalid'], $e->getStatusCode());

        } catch (\Tymon\JWTAuth\Exceptions\JWTException $e) {

            return response()->json(['token_absent'], $e->getStatusCode());

        }
        return response()->json(['estatus' => 'ok'], 200);
    }

    /**
     * Get a validator for an incoming registration request.
     *
     * @param  array $data
     * @return \Illuminate\Contracts\Validation\Validator
     */
    protected
    function validatorUser(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'login' => 'required|max:255|unique:users',
            'password' => 'required|min:6',
        ]);
    }

    protected
    function validatorTranspor(array $data)
    {
        return Validator::make($data, [
            'nombre' => 'required|max:255',
            'apellido' => 'required|max:255',
            'email' => 'required|email|max:255|unique:users',
            'login' => 'required|max:255|unique:users',
            'tipo_dni' => 'required|max:255',
            'dni' => 'required|max:255|unique:users',
            'tipo_licencia' => 'required|max:255',
            'num_seguridad' => 'required|max:255|unique:users',
            'password' => 'required|min:6',
        ]);
    }

    /**
     * Create a new user instance after a valid registration.
     *
     * @param  array $data
     * @return User
     */
    protected
    function createUser(array $data)
    {
        $id_push = "";
        if (array_key_exists('id_push', $data)) {
            $id_push = $data['id_push'];
        }
        $user = new User ([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'login' => $data['login'],
            'password' => bcrypt($data['password']),
            'email' => $data['email'],
            'fecha_nac' => $data['fecha_nac'],
            'telefono' => $data['telefono'],
            'id_push' => $id_push
        ]);
        $user->tipo_user_id = $data['tipo_user_id'];
        $user->save();
        $credentials = array('login' => $data['login'],
            'password' => $data['password']);

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        // all good so return the token
        return array('user' => $user, 'token' => $token);
    }

    protected
    function createTranspor(array $data)
    {
        $id_push = "";
        if (array_key_exists('id_push', $data)) {
            $id_push = $data['id_push'];
        }
        $user = new User ([
            'nombre' => $data['nombre'],
            'apellido' => $data['apellido'],
            'login' => $data['login'],
            'password' => bcrypt($data['password']),
            'email' => $data['email'],
            'fecha_nac' => $data['fecha_nac'],
            'telefono' => $data['telefono'],
            'tipo_dni' => $data['tipo_dni'],
            'tipo_licencia' => $data['tipo_licencia'],
            'dni' => $data['dni'],
            'id_push' => $id_push,
            'num_seguridad' => $data['num_seguridad'],
        ]);
        $user->tipo_user_id = $data['tipo_user_id'];
        $user->save();
        $credentials = array('login' => $data['login'],
            'password' => $data['password']);

        try {
            // attempt to verify the credentials and create a token for the user
            if (!$token = JWTAuth::attempt($credentials)) {
                return response()->json(['error' => 'invalid_credentials'], 401);
            }
        } catch (JWTException $e) {
            // something went wrong whilst attempting to encode the token
            return response()->json(['error' => 'could_not_create_token'], 500);
        }
        // all good so return the token
        return array('user' => $user, 'token' => $token);
    }

    /**
     * Handle a registration request for the application.
     *
     * @param  \Illuminate\Http\Request $request
     * @return \Illuminate\Http\Response
     */
    public
    function postRegister(Request $request)
    {
        if ($request->input('tipo_user_id') == Config::get('constants.TIPO_CLIENTE')) {

            $validator = $this->validatorUser($request->all());
            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            return response()->json($this->createUser($request->all()), 201);

        } else if ($request->input('tipo_user_id') == Config::get('constants.TIPO_TRANSPORTISTA')) {

            $validator = $this->validatorTranspor($request->all());
            if ($validator->fails()) {
                $messages = $validator->errors();
                return response()->json(['error' => $messages], 400);
            }

            return response()->json($this->createTranspor($request->all()), 201);
        } else {
            return response()->json(['error' => 'Unauthorized_User'], 403);
        }
    }
}
