<?php

namespace App\Services;

use App\Models\Client;

class EventParserService
{
    /**
     * Parse event name to extract client name and service
     * Expected format: "Client Name - Service Name" or just "Service Name"
     */
    public static function parseEventName(string $eventName): array
    {
        $clientName = null;
        $serviceName = $eventName; // Default to full name as service
        
        // Try to parse "Client Name - Service Name" format
        if (strpos($eventName, ' - ') !== false) {
            $parts = explode(' - ', $eventName, 2);
            $clientName = trim($parts[0]);
            $serviceName = trim($parts[1]);
        }
        // If no " - " separator found, treat the entire string as service name
        // and leave client_name as null
        
        return [
            'client_name' => $clientName,
            'service_name' => $serviceName
        ];
    }

    /**
     * Create appointment with proper client and service parsing
     */
    public static function createAppointmentFromEvent($event, $start, $duration): array
    {
        $parsedName = self::parseEventName($event->name);
        $clientId = null;
        
        // Find or create client if we extracted a name
        if ($parsedName['client_name']) {
            // First try to find by name only
            $client = Client::where('name', $parsedName['client_name'])->first();
            
            if (!$client) {
                // Create client without phone number (will be set manually later)
                $client = Client::create([
                    'name' => $parsedName['client_name'],
                    'phone' => null, // Don't set phone from Google Calendar
                    'status' => 'Green'
                ]);
            }
            
            $clientId = $client->id;
        }

        return [
            'client_id' => $clientId,
            'service' => $parsedName['service_name'],
            'start_time' => $start,
            'duration' => $duration,
            'status' => 'confirmed',
            'attendance_status' => 'pending',
            'notes' => null,
            'event_id' => $event->id,
        ];
    }

    /**
     * Fetch Google Calendar events with SSL error handling
     */
    public static function fetchGoogleCalendarEvents(): array
    {
        try {
            // First try using our custom GoogleCalendarService
            $calendarService = new GoogleCalendarService();
            
            if (!$calendarService->isConfigured()) {
                return [
                    'success' => false,
                    'events' => collect([]),
                    'error' => 'Google Calendar credentials not found. Please check your Google credentials configuration.'
                ];
            }
            
            $result = $calendarService->getEvents();
            
            if ($result['success']) {
                // Convert Google API events to our expected format
                $events = collect($result['events'])->map(function ($event) {
                    return (object)[
                        'id' => $event->getId(),
                        'summary' => $event->getSummary(),
                        'startDateTime' => $event->getStart()->getDateTime() ?: $event->getStart()->getDate(),
                        'endDateTime' => $event->getEnd()->getDateTime() ?: $event->getEnd()->getDate(),
                        'updated' => $event->getUpdated()
                    ];
                });
                
                return [
                    'success' => true,
                    'events' => $events,
                    'error' => null
                ];
            }
            
            return $result;
            
        } catch (\Exception $e) {
            // Fallback to Spatie package with SSL handling
            try {
                // Try to get Google Calendar events using Spatie package
                $googleEvents = \Spatie\GoogleCalendar\Event::get();
                
                return [
                    'success' => true,
                    'events' => $googleEvents,
                    'error' => null
                ];
            } catch (\Exception $spatieException) {
                // Check if it's an SSL certificate error
                $errorMessage = $spatieException->getMessage();
                if (strpos($errorMessage, 'SSL certificate problem') !== false || 
                    strpos($errorMessage, 'cURL error 60') !== false) {
                    $errorMessage = 'SSL Certificate Error: Add GOOGLE_SSL_VERIFY=false to your .env file to bypass SSL verification in development.';
                }
                
                return [
                    'success' => false,
                    'events' => collect([]),
                    'error' => $errorMessage
                ];
            }
        }
    }

    /**
     * Sync Google Calendar events with database appointments
     * This method handles both creation of new appointments and updates of existing ones
     */
    public static function syncGoogleCalendarEvents($googleEvents = null): array
    {
        $syncResults = [
            'created' => 0,
            'updated' => 0,
            'errors' => []
        ];

        // If no events provided, try to fetch them
        if ($googleEvents === null) {
            $fetchResult = self::fetchGoogleCalendarEvents();
            if (!$fetchResult['success']) {
                $syncResults['errors'][] = 'Failed to fetch Google Calendar events: ' . $fetchResult['error'];
                return $syncResults;
            }
            $googleEvents = $fetchResult['events'];
        }

        // Check if we have any events to process
        if (empty($googleEvents) || $googleEvents->isEmpty()) {
            $syncResults['errors'][] = 'No Google Calendar events found to sync';
            return $syncResults;
        }

        foreach ($googleEvents as $event) {
            try {
                // Try to find an existing appointment with this event_id
                $appointment = \App\Models\Appointment::with('client')->where('event_id', $event->id)->first();

                // Handle different date formats from Google Calendar
                $startTime = $event->startDateTime ?? $event->start ?? null;
                $endTime = $event->endDateTime ?? $event->end ?? null;
                
                if (!$startTime) {
                    $syncResults['errors'][] = [
                        'event_id' => $event->id,
                        'error' => 'Event missing start time'
                    ];
                    continue;
                }
                
                $start = \Carbon\Carbon::parse($startTime)->timezone('UTC');
                $end = $endTime ? \Carbon\Carbon::parse($endTime)->timezone('UTC') : $start->copy()->addHour();
                $duration = $start->diffInMinutes($end);

                // Parse the event name to get client and service data
                $eventSummary = $event->summary ?? '';
                $parsedEvent = self::parseEventName($eventSummary);

                if (!$appointment) {
                    // Create new appointment - now accepts single word as service name
                    if ($parsedEvent['service_name']) {
                        $clientId = null;
                        
                        // Only create/find client if we have a parsed client name
                        if ($parsedEvent['client_name']) {
                            // Find existing client first (case-insensitive search)
                            $client = \App\Models\Client::whereRaw('LOWER(name) = LOWER(?)', [$parsedEvent['client_name']])->first();
                            
                            if (!$client) {
                                // Only create client if name doesn't exist (avoid duplicates)
                                $client = \App\Models\Client::create([
                                    'name' => $parsedEvent['client_name'],
                                    'phone' => null, // Don't set phone from Google Calendar
                                    'status' => 'Green'
                                ]);
                            }
                            
                            $clientId = $client->id;
                        }
                        
                        \App\Models\Appointment::create([
                            'client_id' => $clientId, // Can be null for single word services
                            'service' => $parsedEvent['service_name'],
                            'start_time' => $start,
                            'duration' => $duration,
                            'status' => 'confirmed',
                            'attendance_status' => 'pending',
                            'event_id' => $event->id,
                        ]);
                        
                        $syncResults['created']++;
                    }
                } else {
                    // Check if the appointment needs updating
                    $needsUpdate = false;
                    $updateData = [];

                    // Check if start time changed
                    if ($appointment->start_time->format('Y-m-d H:i:s') !== $start->format('Y-m-d H:i:s')) {
                        $updateData['start_time'] = $start;
                        $needsUpdate = true;
                    }

                    // Check if duration changed
                    if ($appointment->duration != $duration) {
                        $updateData['duration'] = $duration;
                        $needsUpdate = true;
                    }

                    // Check if service name changed (after parsing)
                    if ($appointment->service !== $parsedEvent['service_name']) {
                        $updateData['service'] = $parsedEvent['service_name'];
                        $needsUpdate = true;
                    }

                    // Handle client name changes - only if we have a client name and it changed
                    if ($parsedEvent['client_name']) {
                        $currentClient = $appointment->client;
                        
                        // Only update client if we have a current client and the name actually changed
                        if (!$currentClient || strtolower($currentClient->name) !== strtolower($parsedEvent['client_name'])) {
                            // First try to find existing client with the new name (case-insensitive)
                            $client = \App\Models\Client::whereRaw('LOWER(name) = LOWER(?)', [$parsedEvent['client_name']])->first();
                            
                            if (!$client) {
                                // Create client without phone number (will be set manually later)
                                $client = \App\Models\Client::create([
                                    'name' => $parsedEvent['client_name'],
                                    'phone' => null, // Don't set phone from Google Calendar
                                    'status' => 'Green'
                                ]);
                            }

                            $updateData['client_id'] = $client->id;
                            $needsUpdate = true;
                        }
                    } else {
                        // If no client name in the event, set client_id to null
                        if ($appointment->client_id !== null) {
                            $updateData['client_id'] = null;
                            $needsUpdate = true;
                        }
                    }

                    // Update the appointment if any changes detected
                    if ($needsUpdate) {
                        $appointment->update($updateData);
                        $syncResults['updated']++;
                    }
                }
            } catch (\Exception $e) {
                $syncResults['errors'][] = [
                    'event_id' => $event->id,
                    'error' => $e->getMessage()
                ];
            }
        }

        return $syncResults;
    }
}