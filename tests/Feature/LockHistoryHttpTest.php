<?php

namespace Tests\Feature;

use Tests\Base\BaseTestCase;
use Tests\Base\BaseTestMethods;

class LockHistoryHttpTest extends BaseTestCase
{
    use BaseTestMethods;

    protected function setUp(): void
    {
        parent::setUp();

        $this->authenticateUser();
    }

    public function test_index(): void
    {
        $this->transaction(function () {
            $lock = $this->createOrUpdateLock(collect(
                [
                    'mac_address' => '00:0d:83:b1:c0:8e',
                    'name' => 'Lock',
                ]
            ));

            $this->createLockHistory($lock, 'The door was opened!');
            $this->createLockHistory($lock, 'The door was locked!');

            $response = $this->authenticatedRequest()
                ->withHeader('Accept', 'application/json')
                ->get("lock-histories?with_relations=user,lock");

            $response->assertStatus(200);

            $response->assertJsonStructure(
                [
                    'data' => [
                        [
                            'id',
                            'user_id',
                            'lock_id',
                            'description',
                            'updated_at',
                            'created_at',

                            'user' => [
                                'id',
                                'name',
                                'email',
                                'login',
                            ],

                            'lock' => [
                                'id',
                                'created_by_user_id',
                                'mac_address',
                                'state',
                            ],
                        ]
                    ]
                ]
            );

            $lockHistories = json_decode($response->getContent())->data;

            $this->assertCount(2, $lockHistories);
        });
    }
}
