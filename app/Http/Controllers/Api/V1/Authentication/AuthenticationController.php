<?php

namespace App\Http\Controllers\Api\V1\Authentication;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use App\Http\Requests\Auth\UserRegistrationRequest;
use App\Models\User;
use Illuminate\Contracts\Foundation\Application;
use Illuminate\Contracts\Routing\ResponseFactory;
use Illuminate\Cookie\CookieJar;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Response;

class AuthenticationController extends Controller
{

    /**
     * Attempts multi-authentication through different guards
     *
     * @param LoginRequest $loginRequest
     * @return Application|ResponseFactory|\Illuminate\Http\Response
     */
    public function login(LoginRequest $loginRequest): \Illuminate\Http\Response|Application|ResponseFactory
    {

//      Stores validated request
        $validated = $loginRequest->validated();

//      Attempts to find email in authenticatable models, returns false if fails
        $user = User::where('email', $validated['email'])->first() ?? false;


//      Attempts authentication, returns response with user information and JWT cookie attached if valid
        if ($user) {

            if (!Auth::guard('user')->attempt($validated)) {
                return response([
                    'message' => 'Authentication Failed'
                ], Response::HTTP_UNAUTHORIZED);
            }

            return response([
                'message' => 'Successfully Logged In',
                'userInfo' => Auth::guard('user')->user(),
                'role' => "USER"
            ], Response::HTTP_OK)->withCookie($this->createJwtToken($user));
        }

        return response([
            'message' => 'Invalid Credentials',
        ], Response::HTTP_UNAUTHORIZED);

    }

    /**
     * Based on the Authenticatable model passed, creates a JWT token lasting 24 hours
     *
     * @param $authenticatable
     * @param string[] $abilities
     * @return CookieJar|Cookie|Application
     */
    protected function createJwtToken($authenticatable, array $abilities = ['*']): CookieJar|Cookie|Application
    {
//      Stores application name
        $applicationName = env('APP_NAME');

//      Creates token using application name
        $token = $authenticatable->createToken($applicationName, $abilities)->plainTextToken;

//      Returns cookie
        return cookie('jwt', $token, 60 * 24);
    }

    public function registerUser(UserRegistrationRequest $userRegistrationRequest): \Illuminate\Http\Response|Application|ResponseFactory
    {

//      Stores validated request
        $validated = $userRegistrationRequest->validated();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password'])
        ]);

        return response([
            'message' => 'Your Account Was Created Successfully! You May Now Login',
            'user' => $user
        ], Response::HTTP_OK);
    }
}
