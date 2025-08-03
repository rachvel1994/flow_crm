<?php

namespace App\Observers;

use App\Models\User;

class UserObserver
{
    public function creating(User $user): void
    {
        if (empty($user->password)) {
            $user->password = generateSecurePassword();
        }

        $user->created_by = auth()?->id();
    }
}
