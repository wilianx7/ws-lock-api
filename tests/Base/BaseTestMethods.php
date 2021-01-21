<?php

namespace Tests\Base;

use App\Actions\User\CreateOrUpdateUserAction;
use App\DTOs\UserDTO;
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

    protected function createOrUpdateUser(Collection $data): User
    {
        $createOrUpdateUserAction = app()->make(CreateOrUpdateUserAction::class);
        $userDTO = UserDTO::fromCollection(collect($this->generateUserPayload($data)['user_data']));

        return $createOrUpdateUserAction->execute($userDTO);
    }
}
