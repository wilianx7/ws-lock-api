<?php

namespace Tests\Feature;

use App\Enums\LockStateEnum;
use App\Models\LockHistory;
use App\Models\User;
use Tests\Base\BaseTestCase;
use Tests\Base\BaseTestMethods;

class MqttHttpTest extends BaseTestCase
{
    use BaseTestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateUser();
    }

    public function test_it_can_open_door(): void
    {
        $this->transaction(function () {
            $lock = $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put("mqtt/open-door", ['mac_address' => $lock->mac_address]);

            $response->assertStatus(200);

            $this->assertCount(1, LockHistory::all());
            $this->assertEquals(LockStateEnum::OPENED(), $lock->fresh()->state);
            $this->assertEquals(LockHistory::first()->lock_id, $lock->id);
            $this->assertEquals(LockHistory::first()->user_id, User::getAuthenticated()->id);
            $this->assertEquals('Fechadura aberta!', LockHistory::first()->description);
        });
    }

    public function test_it_can_close_door(): void
    {
        $this->transaction(function () {
            $lock = $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put("mqtt/close-door", ['mac_address' => $lock->mac_address]);

            $response->assertStatus(200);

            $this->assertCount(1, LockHistory::all());
            $this->assertEquals(LockStateEnum::LOCKED(), $lock->fresh()->state);
            $this->assertEquals(LockHistory::first()->lock_id, $lock->id);
            $this->assertEquals(LockHistory::first()->user_id, User::getAuthenticated()->id);
            $this->assertEquals('Fechadura trancada!', LockHistory::first()->description);
        });
    }

    public function test_invalid_lock(): void
    {
        $this->transaction(function () {
            $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->put("mqtt/close-door", ['mac_address' => '00:0d:83:b1:c0']);

            $response->assertStatus(401);
        });
    }
}
