<?php

namespace Tests\Base;

use App\Actions\Lock\CreateOrUpdateLockAction;
use App\Actions\User\CreateOrUpdateUserAction;
use App\DTOs\LockDTO;
use App\DTOs\UserDTO;
use App\Models\Lock;
use App\Models\User;
use Illuminate\Support\Collection;

trait BaseTestMethods
{
    protected function generateUserPayload(Collection $data): array
    {
        return [
            'user_data' => [
                'id' => $data->get('id'),
                'name' => $data->get('name'),
                'email' => $data->get('email'),
                'login' => $data->get('login'),
                'password' => $data->get('password'),
            ]
        ];
    }

    protected function generateLockPayload(Collection $data): array
    {
        return [
            'lock_data' => [
                'id' => $data->get('id'),
                'name' => $data->get('name'),
                'mac_address' => $data->get('mac_address'),
            ]
        ];
    }

    protected function createOrUpdateUser(Collection $data): User
    {
        $createOrUpdateUserAction = app()->make(CreateOrUpdateUserAction::class);
        $userDTO = UserDTO::fromCollection(collect($this->generateUserPayload($data)['user_data']));

        return $createOrUpdateUserAction->execute($userDTO);
    }

    protected function createOrUpdateLock(Collection $data): Lock
    {
        $createOrUpdateLockAction = app()->make(CreateOrUpdateLockAction::class);
        $lockDTO = LockDTO::fromCollection(collect($this->generateLockPayload($data)['lock_data']));

        return $createOrUpdateLockAction->execute($lockDTO);
    }
}
