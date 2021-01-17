<?php

namespace App\Actions\LockHistory;

use App\Models\Lock;
use App\Models\LockHistory;
use App\Models\User;

class CreateLockHistoryAction
{
    public function execute(Lock $lock, string $description): LockHistory
    {
        return LockHistory::create([
            'user_id' => User::getAuthenticated()->id,
            'lock_id' => $lock->id,
            'description' => $description
        ]);
    }
}
