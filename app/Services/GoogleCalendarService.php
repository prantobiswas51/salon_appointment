<?php
namespace App\Services;

use Google\Client;
use Google\Service\Calendar;
use Google\Service\Calendar as GoogleCalendar;
use Google\Service\Calendar\Event as GoogleEvent;
use GuzzleHttp\Client as GuzzleClient;

class GoogleCalendarService
{
    protected $client;
    protected $service;
    
    public static function client(): Client
    {
        $client = new Client();
        $client->setAuthConfig(storage_path('app/google-calendar/service-account-credentials.json'));
        $client->setScopes([Calendar::CALENDAR]);
        $client->setAccessType('offline');
        $client->setPrompt('consent');
        
        // Configure HTTP client with SSL options for development
        if (config('app.env') !== 'production') {
            $guzzleClient = new GuzzleClient([
                'verify' => env('GOOGLE_SSL_VERIFY', false), // Allow disabling SSL verification
                'timeout' => 30,
                'connect_timeout' => 10,
            ]);
            
            $client->setHttpClient($guzzleClient);
        }
        
        return $client;
    }
    
    public function __construct()
    {
        $this->client = self::client();
        $this->service = new GoogleCalendar($this->client);
    }
    
    /**
     * Get all events from the configured calendar with proper error handling
     */
    public function getEvents(): array
    {
        try {
            $calendarId = config('google-calendar.calendar_id', 'primary');
            
            $events = $this->service->events->listEvents($calendarId, [
                'timeMin' => now()->subDays(30)->toISOString(),
                'timeMax' => now()->addDays(30)->toISOString(),
                'singleEvents' => true,
                'orderBy' => 'startTime'
            ]);
            
            return [
                'success' => true,
                'events' => $events->getItems(),
                'error' => null
            ];
        } catch (\Google\Service\Exception $e) {
            return [
                'success' => false,
                'events' => [],
                'error' => 'Google Calendar API Error: ' . $e->getMessage()
            ];
        } catch (\Exception $e) {
            $errorMessage = $e->getMessage();
            
            // Check for common SSL errors
            if (strpos($errorMessage, 'SSL certificate') !== false || 
                strpos($errorMessage, 'cURL error 60') !== false) {
                $errorMessage = 'SSL Certificate Error: For development, add GOOGLE_SSL_VERIFY=false to your .env file to bypass SSL verification.';
            }
            
            return [
                'success' => false,
                'events' => [],
                'error' => $errorMessage
            ];
        }
    }
    
    /**
     * Check if the service is properly configured
     */
    public function isConfigured(): bool
    {
        return file_exists(storage_path('app/google-calendar/service-account-credentials.json'));
    }
}
