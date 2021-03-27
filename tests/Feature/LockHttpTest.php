<?php

namespace Tests\Feature;

use App\Enums\LockStateEnum;
use App\Models\Lock;
use App\Models\User;
use DB;
use Tests\Base\BaseTestCase;
use Tests\Base\BaseTestMethods;

class LockHttpTest extends BaseTestCase
{
    use BaseTestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateUser();
    }

    public function test_it_can_update_lock(): void
    {
        $this->transaction(function () {
            /** Basic test */

            $lock = $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $this->assertNotNull($lock->id);
            $this->assertCount(1, $lock->users);

            $newUserOne = $this->createOrUpdateUser(collect(
                [
                    'name' => 'User A',
                    'email' => 'a@a.com',
                    'login' => 'user_a',
                    'password' => 'password_A'
                ]
            ));

            $lockPayload = $this->generateLockPayload(collect(
                [
                    'id' => $lock->id,
                    'mac_address' => 'edit mac_address',
                    'name' => 'Lock Edit',
                    'users' => [
                        [
                            'id' => $newUserOne->id
                        ]
                    ]
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put('locks/create-or-update', $lockPayload);

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'mac_address',
                        'updated_at',
                        'created_at',
                    ]
                ]
            );

            $lockData = json_decode($response->getContent())->data;

            $lock = $lock->fresh(['users']);

            $this->assertNotNull($lockData->id);
            $this->assertEquals(User::getAuthenticated()->id, $lockData->created_by_user_id);
            $this->assertEquals($lockData->id, $lock->id);
            $this->assertEquals(LockStateEnum::LOCKED(), $lockData->state);
            $this->assertEquals('00:0d:83:b1:c0:8e', $lockData->mac_address);
            $this->assertCount(2, DB::table('user_has_locks')->get());
            $this->assertCount(2, $lock->users);
            $this->assertEquals($newUserOne->id, $lock->users->get(0)->id);
            $this->assertEquals(User::getAuthenticated()->id, $lock->users->get(1)->id);
            $this->assertEquals($newUserOne->id, DB::table('user_has_locks')->get()->get(0)->user_id);
            $this->assertEquals('Lock Edit', DB::table('user_has_locks')->get()->get(0)->lock_name);
            $this->assertEquals(User::getAuthenticated()->id, DB::table('user_has_locks')->get()->get(1)->user_id);
            $this->assertEquals('Lock Edit', DB::table('user_has_locks')->get()->get(1)->lock_name);

            /** Add newUserOne to lock test */

            $this->createOrUpdateLock(collect(
                [
                    'id' => $lock->id,
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                    'users' => [
                        [
                            'id' => $newUserOne->id
                        ]
                    ]
                ]
            ));

            $lock = $lock->fresh(['users']);

            $this->assertCount(2, DB::table('user_has_locks')->get());
            $this->assertCount(2, $lock->users);
            $this->assertEquals($newUserOne->id, $lock->users->get(0)->id);
            $this->assertEquals(User::getAuthenticated()->id, $lock->users->get(1)->id);
            $this->assertEquals($newUserOne->id, DB::table('user_has_locks')->get()->get(0)->user_id);
            $this->assertEquals('Lock Edit', DB::table('user_has_locks')->get()->get(0)->lock_name);
            $this->assertEquals(User::getAuthenticated()->id, DB::table('user_has_locks')->get()->get(1)->user_id);
            $this->assertEquals('Lock', DB::table('user_has_locks')->get()->get(1)->lock_name);

            /** Add newUserTwo and remove newUserOne of lock */

            $newUserTwo = $this->createOrUpdateUser(collect(
                [
                    'name' => 'User B',
                    'email' => 'b@b.com',
                    'login' => 'user_b',
                    'password' => 'password_B'
                ]
            ));

            $this->createOrUpdateLock(collect(
                [
                    'id' => $lock->id,
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                    'users' => [
                        [
                            'id' => $newUserTwo->id
                        ]
                    ]
                ]
            ));

            $lock = $lock->fresh(['users']);

            $this->assertCount(2, DB::table('user_has_locks')->get());
            $this->assertCount(2, $lock->users);
            $this->assertEquals($newUserTwo->id, $lock->users->get(0)->id);
            $this->assertEquals(User::getAuthenticated()->id, $lock->users->get(1)->id);
            $this->assertEquals($newUserTwo->id, DB::table('user_has_locks')->get()->get(0)->user_id);
            $this->assertEquals('Lock', DB::table('user_has_locks')->get()->get(0)->lock_name);
            $this->assertEquals(User::getAuthenticated()->id, DB::table('user_has_locks')->get()->get(1)->user_id);
            $this->assertEquals('Lock', DB::table('user_has_locks')->get()->get(1)->lock_name);

            /** Change lock name logged with newUserTwo */

            $this->authenticateUser($newUserTwo->id);

            $this->createOrUpdateLock(collect(
                [
                    'id' => $lock->id,
                    'name' => 'New user two lock',
                ]
            ));

            $lock = $lock->fresh(['users']);

            $this->assertCount(2, DB::table('user_has_locks')->get());
            $this->assertCount(2, $lock->users);
            $this->assertEquals($newUserTwo->id, $lock->users->get(1)->id);
            $this->assertEquals($newUserTwo->id, DB::table('user_has_locks')->get()->get(1)->user_id);
            $this->assertEquals('New user two lock', DB::table('user_has_locks')->get()->get(1)->lock_name);
            $this->assertEquals('Lock', DB::table('user_has_locks')->get()->get(0)->lock_name);
        });
    }

    public function test_index(): void
    {
        $this->transaction(function () {
            $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock One',
                ]
            ));

            $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:07:77:b1:c0:8D',
                    'name' => 'Lock Two',
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->get("locks?with_relations=createdByUser,users");

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'data' => [
                            [
                                'id',
                                'mac_address',
                                'updated_at',
                                'created_at',
                                'created_by_user_id',

                                'users' => [
                                    [
                                        'id',
                                        'name',
                                        'email',
                                        'login',
                                        'pivot'
                                    ]
                                ],

                                'created_by_user' => [
                                        'id',
                                        'name',
                                        'email',
                                        'login',
                                ],
                            ]
                    ]
                ]
            );

            $locks = json_decode($response->getContent())->data;

            $this->assertCount(2, $locks);
        });
    }

    public function test_show(): void
    {
        $this->transaction(function () {
            $lock = $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $this->assertNotNull($lock->id);

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->get("locks/$lock->id?with=users");

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'mac_address',
                        'updated_at',
                        'created_at',

                        'users' => [
                            [
                                'id',
                                'name',
                                'email',
                                'login',
                                'pivot'
                            ]
                        ]
                    ]
                ]
            );

            $lockData = json_decode($response->getContent())->data;

            $this->assertNotNull($lockData->id);
            $this->assertEquals(User::getAuthenticated()->id, $lockData->created_by_user_id);
            $this->assertEquals($lockData->id, $lock->id);
            $this->assertEquals(LockStateEnum::LOCKED(), $lockData->state);
            $this->assertEquals('00:0d:83:b1:c0:8e', $lockData->mac_address);
            $this->assertCount(1, DB::table('user_has_locks')->get());
            $this->assertEquals('Lock', DB::table('user_has_locks')->first()->lock_name);
            $this->assertCount(1, $lockData->users);
            $this->assertEquals('admin', $lockData->users[0]->name);
            $this->assertEquals('Lock', $lockData->users[0]->pivot->lock_name);
        });
    }

    public function test_destroy(): void
    {
        $this->transaction(function () {
            $lock = $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $this->assertNotNull($lock->id);

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->delete("locks/$lock->id");

            $response->assertStatus(204);

            $this->assertCount(0, Lock::all());
            $this->assertCount(0, DB::table('user_has_locks')->get());
        });
    }

    public function test_if_can_destroy_only_relation(): void
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

            $lock = $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                    'users' => [
                        [
                            'id' => $userA->id
                        ]
                    ]
                ]
            ));

            $this->assertNotNull($lock->id);
            $this->assertCount(2, DB::table('user_has_locks')->get());

            $this->authenticateUser($userA->id);

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->delete("locks/$lock->id");

            $response->assertStatus(204);

            $this->assertCount(1, Lock::all());
            $this->assertCount(1, DB::table('user_has_locks')->get());
            $this->assertEquals(1, DB::table('user_has_locks')->first()->user_id);
        });
    }

    public function test_it_can_create_lock(): void
    {
        $this->transaction(function () {
            $lockPayload = $this->generateLockPayload(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put('locks/create-or-update', $lockPayload);

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'data' => [
                        'id',
                        'created_by_user_id',
                        'mac_address',
                        'updated_at',
                        'created_at',
                    ]
                ]
            );

            $lockData = json_decode($response->getContent())->data;

            $this->assertNotNull($lockData->id);
            $this->assertEquals(User::getAuthenticated()->id, $lockData->created_by_user_id);
            $this->assertEquals(LockStateEnum::LOCKED(), $lockData->state);
            $this->assertEquals('00:0d:83:b1:c0:8e', $lockData->mac_address);
            $this->assertCount(1, DB::table('user_has_locks')->get());
            $this->assertEquals('Lock', DB::table('user_has_locks')->first()->lock_name);
        });
    }

    public function test_it_cant_create_lock_with_existing_mac_address(): void
    {
        $this->transaction(function () {
            $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $lockPayload = $this->generateLockPayload(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put('locks/create-or-update', $lockPayload);

            $response->assertStatus(500);
        });
    }

    public function test_it_cant_create_lock_when_unauthenticated(): void
    {
        $this->transaction(function () {
            auth('api')->logout();

            $lockPayload = $this->generateLockPayload(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $response = $this->withHeader('Accept', 'application/json')
                ->put('locks/create-or-update', $lockPayload);

            $response->assertStatus(401);
        });
    }
}
