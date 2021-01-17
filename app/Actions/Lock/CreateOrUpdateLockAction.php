<?php

namespace App\Actions\Lock;

use App\DTOs\LockDTO;
use App\Models\Lock;
use App\Models\User;

class CreateOrUpdateLockAction
{
    public function execute(LockDTO $lockDTO): Lock
    {
        $lock = Lock::findOrNew($lockDTO->id);

        $lock->name = $lockDTO->name;

        if (!$lock->id) {
            $lock->mac_address = $lockDTO->mac_address;
        }

        $lock->save();

        $this->syncUserLocks($lock);

        return $lock;
    }

    private function syncUserLocks(Lock $lock): void
    {
        $authenticatedUser = User::getAuthenticated();

        $userLocks = $authenticatedUser->locks->pluck('id');

        $authenticatedUser->locks()->sync($userLocks->push($lock->id)->unique());
    }
}
