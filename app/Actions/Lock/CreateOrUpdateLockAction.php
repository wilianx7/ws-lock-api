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

        if (!$lock->id) {
            $lock->created_by_user_id = User::getAuthenticated()->id;
            $lock->mac_address = $lockDTO->mac_address;

            $lock->save();
        }

        $this->syncUserLocks($lock, $lockDTO->name);

        return $lock->fresh();
    }

    private function syncUserLocks(Lock $lock, string $lockName): void
    {
        $authenticatedUser = User::getAuthenticated();

        $authenticatedUser->locks()->detach($lock->id);

        $authenticatedUser->locks()->attach($lock->id, ['lock_name' => $lockName]);
    }
}
