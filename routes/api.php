<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware('auth:api')->get('/user', function (Request $request) {
    return $request->user();
});

Route::post('/register', 'UsersController@register');
Route::post('/login', 'UsersController@login');

$auth_routes = ['/getme' => 'UsersController@getMe'];

foreach ($auth_routes as $route => $controller) {
    if(!\App\Http\Controllers\UsersController::getAuthUser()) {
        Route::any($route, function () {
            return response()
                ->json('Unauthorized')
                ->setStatusCode(403, 'Unauthorized');
        });
    } else {
        Route::any($route, $controller);
    }
}
