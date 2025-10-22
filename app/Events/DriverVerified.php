<?php

namespace App\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverVerified
{
    use Dispatchable, SerializesModels;

    public $driverId;
    public $verificationData;

    public function __construct($driverId, $verificationData)
    {
        $this->driverId = $driverId;
        $this->verificationData = $verificationData;
    }
}
