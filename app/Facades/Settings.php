<?php

namespace App\Facades;

use Illuminate\Support\Facades\Facade;

class Settings extends Facade
{
    protected static function getFacadeAccessor()
    {
        return \App\Services\SettingsService::class;
    }
}