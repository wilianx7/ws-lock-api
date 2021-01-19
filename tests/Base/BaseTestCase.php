<?php

namespace Tests\Base;

use App\Models\User;
use DB;
use Exception;
use Faker\Factory;
use Faker\Generator;
use Illuminate\Foundation\Console\Kernel;
use Illuminate\Foundation\Testing\TestCase;
use Throwable;

abstract class BaseTestCase extends TestCase
{
    protected ?Generator $faker = null;

    protected function setUp(): void
    {
        parent::setUp();

        $factory = app()->make(Factory::class);
        $this->faker = $factory->create();
    }

    public function createApplication()
    {
        $app = require __DIR__ . '../../../bootstrap/app.php';

        $app->make(Kernel::class)->bootstrap();

        return $app;
    }

    public function transaction($fn)
    {
        DB::beginTransaction();

        try {
            $fn();
            DB::rollBack();
        } catch (Throwable $ex) {
            DB::rollBack();

            throw $ex;
        }

        DB::rollBack();
    }

    protected function authenticateUser(int $userId)
    {
        $user = User::find($userId);

        auth('api')->login($user);
    }

    protected function authenticatedRequest()
    {
        if (!auth('api')->user()) {
            throw new Exception('You must call authenticateUser method in order to generate a authenticatedRequest!');
        }

        return $this->withHeader('Auth', 'Bearer ' . auth('api')->user()->getRememberToken());
    }
}
