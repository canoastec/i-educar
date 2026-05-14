<?php

namespace App\Helpers;

use App\User;

class UserCache
{
    public static function user($id)
    {
        return User::query()->with([
            'person',
            'type',
            'employee',
        ])->find($id);
    }
}
