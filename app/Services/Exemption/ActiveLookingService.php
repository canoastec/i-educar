<?php

namespace App\Services\Exemption;

use App\Events\ActiveLookingCreated;
use App\Models\LegacyActiveLooking;
use App\Models\LegacyRegistration;
use App\Rules\CanStoreActiveLooking;

class ActiveLookingService
{
    public function store(LegacyActiveLooking $activeLooking, LegacyRegistration $registration)
    {
        validator(
            ['active_looking' => [
                'registration' => $registration,
                'active_looking' => $activeLooking,
            ],
            ],
            ['active_looking' => new CanStoreActiveLooking]
        )->validate();

        $activeLooking->save();

        event(new ActiveLookingCreated($activeLooking));

        return $activeLooking;
    }

    public function delete(LegacyActiveLooking $activeLooking)
    {
        $activeLooking->delete();
    }
}
