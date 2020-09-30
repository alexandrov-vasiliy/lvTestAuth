<?php

namespace App\Http\Controllers;

use App\Users;
use Illuminate\Http\Request;

class UsersController extends Controller
{
    //функция ренистрации Request то что мы получаем с формы
    public function register(Request $request) {
        //валидируем поля
        $validator = validator($request->all(), [
            'email' => 'required|email|unique:users',
            'name' => 'required',
            'password' => 'required|min:8'
        ]);

        //если валидация не прошла возвращем json с ошибкой
        if($validator->fails()) {
            return response()
                ->json($validator->errors())
                ->setStatusCode(400, 'Bad Request');
        }
        //создаем нового пользователя 
        $user = new Users();
        $user->email = $request->email;
        $user->name = $request->name;
        $user->password = md5($request->password);//хешируем пароль
        if($user->save()) { //если сохранилось возвращаем json с успехом
            return response()
                ->json('Success Registred')
                ->setStatusCode(201, 'Registred');
        }

        return response() // по дефолту возвращаем json с ошибкой
            ->json($validator->errors())
            ->setStatusCode(400, 'Bad Request');
    }

    //функция логин
    public function login(Request $request) {
        //валидация полей
        $validator = validator($request->all(), [
            'email' => 'required|email',
            'password' => 'required|min:8'
        ]);

        if($validator->fails()) {
            return response()
                ->json($validator->errors())
                ->setStatusCode(400, 'Bad Request');
        }

        //в переменную user заносим юсера у которого email и пароль совпадает с паролем введенным с формы
        $user = Users::where(['email' => $request->email, 'password' => md5($request->password)])->get();
        if(count($user) > 0) {//если в масиве юсер есть 1 юсер 
            $user = $user[0];//убираем все элеменнты кроме 0
            $user->access_token = md5(time());//поле аксес токен получает хеш веремени
            if($user->save()) {//если юсер сохранен возвращаем json с аксес токеном
                return response()
                    ->json(['access_token' => $user->access_token])
                    ->setStatusCode(200, 'Logged');
            }
        }

        // по дефолту возвращаем json с ошибкой
        return response()
            ->json($validator->errors())
            ->setStatusCode(400, 'Bad Request');
    }


    public static function getBearerToken() {
        if(isset($_SERVER['HTTP_AUTHORIZATION'])) {//суперглобальный масив содержит информацию с сервера проверяем есть ли авторизация
            $auth = $_SERVER['HTTP_AUTHORIZATION'];
            
            if(isset($auth)) {
                $auth = explode(' ', $auth);//разбиваем строку на массив
                if(count($auth) > 1) {//проверяем есть ли элементы в этом массиве
                    return $auth[1];//возвращаем 1 элемент массива
                }
            }
        }
        return null;//по дефолту возвращаем null
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
