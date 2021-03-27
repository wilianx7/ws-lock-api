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

        if (!$lock->id && $lockDTO->mac_address) {
            $lock->created_by_user_id = User::getAuthenticated()->id;
            $lock->mac_address = $lockDTO->mac_address;

            $lock->save();
        }

        if ($lock->created_by_user_id == User::getAuthenticated()->id) {
            $this->syncLockUsers($lock, $lockDTO);
        } else {
            $this->updateLockNameForAuthenticatedUser($lock, $lockDTO);
        }

        return $lock->fresh();
    }

    private function syncLockUsers(Lock $lock, LockDTO $lockDTO): void
    {
        $currentRelatedUsers = $lock->users->where('id', '!=', $lock->created_by_user_id)->pluck('id');
        $newRelatedUsers = $lockDTO->users->pluck('id');

        if ($currentRelatedUsers->diff($newRelatedUsers)->count()
            || $newRelatedUsers->diff($currentRelatedUsers)->count()) {
            $lock->users()->detach($currentRelatedUsers->diff($newRelatedUsers));

            foreach ($newRelatedUsers as $newRelatedUser) {
                $lock->users()->attach($newRelatedUser, ['lock_name' => $lockDTO->name]);
            }
        }

        $this->updateLockNameForAuthenticatedUser($lock, $lockDTO);
    }

    private function updateLockNameForAuthenticatedUser(Lock $lock, LockDTO $lockDTO): void
    {
        $authenticatedUser = User::getAuthenticated();

        $authenticatedUser->locks()->detach($lock->id);

        $authenticatedUser->locks()->attach($lock->id, ['lock_name' => $lockDTO->name]);
    }
}
