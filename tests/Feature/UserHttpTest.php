<?php

namespace Tests\Feature;

use App\Models\User;
use Tests\Base\BaseTestCase;
use Tests\Base\BaseTestMethods;

class UserHttpTest extends BaseTestCase
{
    use BaseTestMethods;

    public function test_index(): void
    {
        $this->transaction(function () {
            $this->createOrUpdateUser(collect(
                [
                    'name' => 'User A',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                    'password' => 'password_A'
                ]
            ));

            $response = $this->withHeader('Accept', 'application/json')
                ->get('users');

            $response->assertStatus(401);

            $this->authenticateUser();

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->get('users');

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'data' => [
                        [
                            'name',
                            'login',
                            'email',
                        ]
                    ]
                ]
            );

            $responseData = collect(json_decode($response->getContent())->data);

            $this->assertCount(1, $responseData);
            $this->assertEquals('User A', $responseData->get(0)->name);
        });
    }

    public function test_show(): void
    {
        $this->transaction(function () {
            $response = $this->withHeader('Accept', 'application/json')
                ->get('users/1');

            $response->assertStatus(401);

            $this->authenticateUser();

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->get('users/1');

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'name',
                        'login',
                        'email',
                    ]
                ]
            );

            $responseData = json_decode($response->getContent())->data;

            $this->assertEquals(1, $responseData->id);
            $this->assertEquals('admin', $responseData->name);
        });
    }

    public function test_destroy(): void
    {
        $this->transaction(function () {
            $this->createOrUpdateUser(collect(
                [
                    'name' => 'User A',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                    'password' => 'password_A'
                ]
            ));

            $this->assertCount(2, User::all());

            $response = $this->withHeader('Accept', 'application/json')
                ->delete('users/2');

            $response->assertStatus(401);

            $this->authenticateUser();

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->delete('users/2');

            $response->assertStatus(204);

            $this->assertCount(1, User::all());
            $this->assertEquals('admin', User::first()->name);
        });
    }

    public function test_it_can_create_user_when_unauthenticated(): void
    {
        $this->transaction(function () {
            $userPayload = $this->generateUserPayload(collect(
                [
                    'name' => 'User A',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                    'password' => 'password_A'
                ]
            ));

            $response = $this->withHeader('Accept', 'application/json')
                ->put('users/create-or-update', $userPayload);

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'id',
                    'name',
                    'login',
                    'email',
                    'updated_at',
                    'created_at',
                ]
            );

            $userData = json_decode($response->getContent());

            $this->assertNotNull($userData->id);
            $this->assertNotNull($userData->created_at);
            $this->assertNotNull($userData->updated_at);
            $this->assertEquals('User A', $userData->name);
            $this->assertEquals('a@a.com', $userData->email);
            $this->assertEquals('user_a', $userData->login);
        });
    }

    public function test_it_cant_update_user_when_unauthenticated(): void
    {
        $this->transaction(function () {
            $userA = $this->createOrUpdateUser(collect(
                [
                    'name' => 'User A',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                    'password' => 'password_A'
                ]
            ));

            $this->assertNotNull($userA->id);

            $userPayload = $this->generateUserPayload(collect(
                [
                    'id' => $userA->id,
                    'name' => 'User A Updated',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                ]
            ));

            $response = $this->withHeader('Accept', 'application/json')
                ->put('users/create-or-update', $userPayload);

            $response->assertStatus(401);
        });
    }

    public function test_it_can_create_user_when_authenticated(): void
    {
        $this->transaction(function () {
            $this->authenticateUser();

            $userPayload = $this->generateUserPayload(collect(
                [
                    'name' => 'User A',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                    'password' => 'password_A'
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put('users/create-or-update', $userPayload);

            $response->assertStatus(200);

            $userData = json_decode($response->getContent());

            $this->assertNotNull($userData->id);
            $this->assertEquals('User A', $userData->name);
        });
    }

    public function test_it_can_update_user_when_authenticated(): void
    {
        $this->transaction(function () {
            $this->authenticateUser();

            $userA = $this->createOrUpdateUser(collect(
                [
                    'name' => 'User A',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                    'password' => 'password_A'
                ]
            ));

            $this->assertNotNull($userA->id);

            $userPayload = $this->generateUserPayload(collect(
                [
                    'id' => $userA->id,
                    'name' => 'User A Updated',
                    'email' => 'a@a.com',
                    'login' => 'user_a_updated',
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put('users/create-or-update', $userPayload);

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'id',
                    'name',
                    'login',
                    'email',
                    'updated_at',
                    'created_at',
                ]
            );

            $userData = json_decode($response->getContent());

            $this->assertEquals($userA->id, $userData->id);
            $this->assertEquals('User A Updated', $userData->name);
            $this->assertEquals('a@a.com', $userData->email);
            $this->assertEquals('user_a_updated', $userData->login);
        });
    }
}
