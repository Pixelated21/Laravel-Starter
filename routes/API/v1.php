<?php

use App\Http\Controllers\Api\V1\Authentication\AuthenticationController;
use App\Http\Requests\Auth\UserRegistrationRequest;
use App\Models\User;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthenticationController::class, 'login'])->name('login');
Route::post('/register_user', [AuthenticationController::class, 'registerUser']);

Route::group(['middleware' => 'auth:sanctum', 'prefix' => 'auth'], static function () {
    Route::get('/users', static function (Request $request) {

        return response([
            'data' => $request->user()
        ],200);
    });

    Route::post('/users', static function (UserRegistrationRequest $userRegistrationRequest) {

        $validated = $userRegistrationRequest->validated();

        return User::create($validated);

    });

});
