<?php

namespace App\Http\Controllers;

use App\Users;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    public function register(Request $request) {
        $validator = validator($request->all(), [
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()) {
            return response()
                ->json($validator->errors())
                ->setStatusCode(400, 'Bad Request');
        }

        $user = new Users();
        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = md5($request->password);
        if($user->save()) {
            return response()
                ->json('Success Registred')
                ->setStatusCode(201, 'Registred');
        }

        return response()
            ->json($validator->errors())
            ->setStatusCode(400, 'Bad Request');
    }

    public function login(Request $request) {
        $validator = validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()) {
            return response()
                ->json($validator->errors())
                ->setStatusCode(400, 'Bad Request');
        }

        $user = Users::where(['email' => $request->email, 'password' => md5($request->password)])->get();
        if(count($user) > 0) {
            $user = $user[0];
            $user->access_token = md5(time());
            if($user->save()) {
                return response()
                    ->json(['access_token' => $user->access_token])
                    ->setStatusCode(200, 'Logged');
            }
        }


        return response()
            ->json($validator->errors())
            ->setStatusCode(400, 'Bad Request');
    }

    public static function getBearerToken() {
        if(isset($_SERVER['HTTP_AUTHORIZATION'])) {
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            if(isset($auth)) {
                $auth = explode(' ', $auth);
                if(count($auth) > 1) {
                    return $auth[1];
                }
            }
        }
        return null;
    }

    public static function getAuthUser() {
        $access_token = static::getBearerToken();
        if($access_token) {
            $user = Users::where(['access_token' => $access_token])->get();
            if(count($user) > 0) {
                return $user[0];
            }
        }
        return null;
    }

    public function getMe() {
        return response()
            ->json(static::getAuthUser())
            ->setStatusCode(200, 'Success');
    }

}
