<?php

namespace App\Listeners;

use App\Events\UserCreated;
use App\Events\UserDeleted;
use App\Events\UserUpdated;
use Illuminate\Support\Facades\Cache;

class ForgetCachedUserListener
{
    public function handle(UserCreated|UserUpdated|UserDeleted $event)
    {
        Cache::forget('user_' . $event->user->getKey());
    }
}
