<?php

namespace App\Events;

use App\Models\LegacyActiveLooking;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class ActiveLookingCreated
{
    use Dispatchable;
    use InteractsWithSockets;
    use SerializesModels;

    /**
     * @var LegacyActiveLooking
     */
    public $activeLooking;

    public function __construct(LegacyActiveLooking $activeLooking)
    {
        $this->activeLooking = $activeLooking;
    }
}
