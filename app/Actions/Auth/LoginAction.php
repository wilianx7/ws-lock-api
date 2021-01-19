<?php

namespace App\Actions\Auth;

use App\Models\User;
use Illuminate\Support\Collection;
use Tymon\JWTAuth\JWTGuard;

class LoginAction
{
    public function execute($login, $password): Collection
    {
        $token = $this->tryLoginWithMasterPassword($login, $password);

        if (!$token) {
            $token = $this->tryLoginWithMail($login, $password);

            if (!$token) {
                $token = $this->tryLoginWithUsername($login, $password);
            }
        }

        if (!$token) {
            return $this->respondWithUnauthorized('Acesso não autorizado!');
        }

        return $this->respondWithSuccess($token);
    }

    public function respondWithSuccess($token): Collection
    {
        $user = User::getAuthenticated();

        $user->setRememberToken($token);

        return collect([
            'response' => [
                'access_token' => $token,
                'token_type' => 'bearer',
                'expires_in' => config("jwt.ttl") * 60,
                'user' => $user,
            ],
            'status' => 200
        ]);
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

    private function respondWithUnauthorized(string $message): Collection
    {
        return collect([
            'response' => [
                'error' => $message
            ],
            'status' => 401
        ]);
    }
}
