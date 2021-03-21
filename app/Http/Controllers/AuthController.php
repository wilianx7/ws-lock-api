<?php

namespace App\Http\Controllers;

use App\Actions\Auth\LoginAction;
use App\Http\Requests\AuthRequest;
use Tymon\JWTAuth\JWTGuard;

class AuthController extends Controller
{
    private LoginAction $loginAction;

    public function __construct(LoginAction $loginAction)
    {
        $this->loginAction = $loginAction;
    }

    public function login(AuthRequest $request)
    {
        $response = $this->loginAction->execute($request->input('login'), $request->input('password'));

        if (!$response) {
            return response()->json('Unauthorized access', 401);
        }

        return response()->json($response, 200);
    }

    public function validateJwt()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth('api')->logout();

        return response()->json('', 200);
    }

    public function refresh()
    {
        /** @var JWTGuard $auth */
        $auth = auth('api');

        $response = $this->loginAction->makeSuccessResponse($auth->refresh());

        return response()->json($response, 200);
    }
}
