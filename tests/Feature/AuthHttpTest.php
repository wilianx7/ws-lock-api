<?php

namespace Tests\Feature;

use App\Actions\Auth\LoginAction;
use App\Models\User;
use Tests\Base\BaseTestCase;

class AuthHttpTest extends BaseTestCase
{
    private LoginAction $loginAction;

    protected function setUp(): void
    {
        parent::setUp();

        $this->loginAction = app()->make(LoginAction::class);
    }

    public function test_it_can_login_with_valid_credentials(): void
    {
        $this->transaction(function () {
            $authPayload = [
                'login' => 'admin',
                'password' => 'p#mB2%f;<cnc(Vx:',
            ];

            $response = $this->withHeader('Accept', 'application/json')
                ->post('login', $authPayload);

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'access_token',
                    'token_type',
                    'expires_in',
                    'user',
                ]
            );

            $this->assertEquals(User::first()->id, User::getAuthenticated()->id);
        });
    }

    public function test_it_cant_login_with_invalid_credentials(): void
    {
        $this->transaction(function () {
            $authPayload = [
                'login' => 'admin',
                'password' => 'invalid password',
            ];

            $response = $this->withHeader('Accept', 'application/json')
                ->post('login', $authPayload);

            $response->assertStatus(401);

            $this->assertNull(User::getAuthenticated()->id);
        });
    }

    public function test_it_is_valid_jwt(): void
    {
        $this->transaction(function () {
            $this->loginAction->execute('admin', 'p#mB2%f;<cnc(Vx:');

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->get('validate');

            $response->assertStatus(200);

            $authenticatedUser = json_decode($response->getContent());

            $this->assertEquals(User::getAuthenticated()->id, $authenticatedUser->id);
        });
    }

    public function test_it_is_invalid_jwt(): void
    {
        $this->transaction(function () {
            $this->loginAction->execute('admin', 'invalid password');

            $response = $this->withHeader('Accept', 'application/json')
                ->get('validate');

            $response->assertStatus(401);
        });
    }

    public function test_it_can_refresh_jwt_token(): void
    {
        $this->transaction(function () {
            $this->loginAction->execute('admin', 'p#mB2%f;<cnc(Vx:');

            $originalToken = auth('api')->user()->getRememberToken();

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->post('refresh');

            $response->assertStatus(200);

            $newToken = auth('api')->user()->getRememberToken();

            $this->assertNotEquals($originalToken, $newToken);
        });
    }

    public function test_it_can_logout(): void
    {
        $this->transaction(function () {
            $this->loginAction->execute('admin', 'p#mB2%f;<cnc(Vx:');

            $this->assertNotNull(User::getAuthenticated()->id);

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->post('logout');

            $response->assertStatus(200);

            $this->assertNull(User::getAuthenticated()->id);
        });
    }
}
