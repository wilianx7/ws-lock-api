<?php

namespace App\Actions\User;

use App\DTOs\UserDTO;
use App\Models\User;

class CreateOrUpdateUserAction
{
    public function execute(UserDTO $userDTO): User
    {
        $user = User::findOrNew($userDTO->id);

        if ($user->exists && !User::getAuthenticated()->id) {
            abort(401, 'Unauthorized action!');
        }

        return $this->createOrUpdateUser($user, $userDTO);
    }

    private function createOrUpdateUser(User $user, UserDTO $userDTO): User
    {
        $user->name = $userDTO->name;
        $user->login = $userDTO->login;
        $user->email = $userDTO->email;

        if ($userDTO->password) {
            $user->password = bcrypt($userDTO->password);
        }

        $user->save();

        return $user;
    }
}
