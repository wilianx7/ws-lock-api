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

        if ($response->get('status') === 401) {
            return response()->json($response->get('response'), 401);
        }

        return response()->json($response->get('response'), 200);
    }

    public function validateJwt()
    {
        return response()->json(auth()->user());
    }

    public function logout()
    {
        auth()->logout();

        return response()->json(['message' => 'Successfully logged out'], 200);
    }

    public function refresh()
    {
        /** @var JWTGuard $auth */
        $auth = auth();

        $response = $this->loginAction->respondWithSuccess($auth->refresh());

        return response()->json($response->get('response'), 200);
    }
}
