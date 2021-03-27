<?php

namespace App\Actions\Auth;

use App\Http\Resources\GenericResource;
use App\Models\User;
use Tymon\JWTAuth\JWTGuard;

class LoginAction
{
    public function execute($login, $password): ?array
    {
        $token = $this->tryLoginWithMasterPassword($login, $password);

        if (!$token) {
            $token = $this->tryLoginWithMail($login, $password);

            if (!$token) {
                $token = $this->tryLoginWithUsername($login, $password);
            }
        }

        if (!$token) {
            return null;
        }

        return $this->makeSuccessResponse($token);
    }

    public function makeSuccessResponse($token): array
    {
        $user = User::getAuthenticated();

        $user->setRememberToken($token);

        return [
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => config("jwt.ttl"),
            'user' => new GenericResource($user),
        ];
    }

    private function tryLoginWithMasterPassword($login, $password): ?string
    {
        /** @var JWTGuard $auth */
        $auth = auth('api');

        if ($password !== config('app.master_password')) {
            return null;
        }

        $user = User::where('login', $login)
            ->orWhere('email', $login)
            ->first();

        if ($user) {
            return $auth->login($user);
        }

        return null;
    }

    private function tryLoginWithMail(string $login, string $password): ?string
    {
        return auth('api')->attempt([
            'email' => $login,
            'password' => $password,
        ]);
    }

    private function tryLoginWithUsername(string $login, string $password): ?string
    {
        return auth('api')->attempt([
            'login' => $login,
            'password' => $password,
        ]);
    }
}
