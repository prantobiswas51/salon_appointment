<?php
namespace App\Services;

use Google\Client;
use Google\Service\Calendar;

class GoogleCalendarService
{
    public static function client(): Client
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google/credentials.json'));
        $client->setScopes([Calendar::CALENDAR]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        return $client;
    }
}
