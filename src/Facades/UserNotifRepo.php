<?php

namespace hpsynapse\moduser\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @method static bool broadcast(string $channel, \Illuminate\Notifications\Notification $notification)
 */
class UserNotifRepo extends Facade
{
    /**
     * Get the registered name of the component.
     *
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return \hpsynapse\moduser\Contracts\UserNotifRepo::class;
    }
}