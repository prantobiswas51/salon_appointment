<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use function Pest\Laravel\json;
use Spatie\GoogleCalendar\Event;
use Illuminate\Support\Facades\Log;
use App\Services\EventParserService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\returnSelf;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Google\Service\Exception as GoogleServiceException;

class AppointmentController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::with('client');

        // Search functionality
        if ($request->filled('q')) {
            $searchTerm = $request->q;
            $query->where(function ($q) use ($searchTerm) {
                $q->where('service', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('notes', 'LIKE', "%{$searchTerm}%")
                    ->orWhere('status', 'LIKE', "%{$searchTerm}%")
                    ->orWhereHas('client', function ($clientQuery) use ($searchTerm) {
                        $clientQuery->where('name', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('phone', 'LIKE', "%{$searchTerm}%")
                            ->orWhere('email', 'LIKE', "%{$searchTerm}%");
                    });
            });
        }

        // Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_time', '<=', $request->date_to);
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // Service filter
        if ($request->filled('service')) {
            $query->where('service', 'LIKE', "%{$request->service}%");
        }

        // Attendance status filter
        if ($request->filled('attendance_status')) {
            $query->where('attendance_status', $request->attendance_status);
        }

        $appointments = $query->orderByDesc('created_at')->paginate(10);

        // Keep search parameters in pagination links
        $appointments->appends($request->only(['q', 'date_from', 'date_to', 'status', 'service', 'attendance_status']));

        return Inertia::render('appointment/index', [
            'appointments' => $appointments,
            'filters' => [
                'q' => $request->q,
                'date_from' => $request->date_from,
                'date_to' => $request->date_to,
                'status' => $request->status,
                'service' => $request->service,
                'attendance_status' => $request->attendance_status,
            ],
        ]);
    }

    public function events()
    {
        try {
            $syncResults = [
                'created' => 0,
                'updated' => 0,
                'errors' => []
            ];

            // ---- Fetch Google Calendar Events ----
            try {
                $calendarService = new \App\Services\GoogleCalendarService();

                if (!$calendarService->isConfigured()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'Google Calendar credentials not found. Please check your Google credentials configuration.',
                        'results' => $syncResults
                    ]);
                }

                $result = $calendarService->getEvents();

                if ($result['success']) {
                    $googleEvents = collect($result['events'])->map(function ($event) {
                        return (object)[
                            'id' => $event->getId(),
                            'summary' => $event->getSummary(),
                            'startDateTime' => $event->getStart()->getDateTime() ?: $event->getStart()->getDate(),
                            'endDateTime' => $event->getEnd()->getDateTime() ?: $event->getEnd()->getDate(),
                            'updated' => $event->getUpdated()
                        ];
                    });
                } else {
                    return response()->json([
                        'success' => false,
                        'message' => 'Failed to fetch events: ' . $result['error'],
                        'results' => $syncResults
                    ]);
                }
            } catch (\Exception $e) {
                try {
                    $googleEvents = \Spatie\GoogleCalendar\Event::get();
                } catch (\Exception $spatieException) {
                    $errorMessage = $spatieException->getMessage();
                    if (strpos($errorMessage, 'SSL certificate problem') !== false || strpos($errorMessage, 'cURL error 60') !== false) {
                        $errorMessage = 'SSL Certificate Error: Add GOOGLE_SSL_VERIFY=false to your .env file to bypass SSL verification in development.';
                    }

                    return response()->json([
                        'success' => false,
                        'message' => $errorMessage,
                        'results' => $syncResults
                    ]);
                }
            }

            // ---- Parse and Sync Events ----
            if (empty($googleEvents) || $googleEvents->isEmpty()) {
                $syncResults['errors'][] = 'No Google Calendar events found to sync';
                return response()->json([
                    'success' => false,
                    'message' => 'No events found',
                    'results' => $syncResults
                ]);
            }

            foreach ($googleEvents as $event) {
                try {
                    // Parse event name -> "Client - Service - Phone"
                    $eventName = $event->summary ?? '';
                    $clientName = null;
                    $serviceName = $eventName;
                    $phone = null;

                    if (substr_count($eventName, ' - ') >= 2) {
                        $parts = explode(' - ', $eventName, 3);
                        $clientName = trim($parts[0]);
                        $serviceName = trim($parts[1]);
                        $phone = trim($parts[2]);
                    } elseif (strpos($eventName, ' - ') !== false) {
                        $parts = explode(' - ', $eventName, 2);
                        $clientName = trim($parts[0]);
                        $serviceName = trim($parts[1]);
                    }

                    $appointment = \App\Models\Appointment::with('client')->where('event_id', $event->id)->first();

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

                    if (!$appointment) {
                        $clientId = null;

                        if ($clientName && $phone) {
                            $client = \App\Models\Client::where('phone', $phone)->first();

                            if ($client) {
                                if (empty($client->name) || strtolower($client->name) !== strtolower($clientName)) {
                                    $client->update(['name' => $clientName]);
                                }
                            } else {
                                $client = \App\Models\Client::create([
                                    'name' => $clientName,
                                    'phone' => $phone,
                                    'status' => 'Green',
                                ]);
                            }

                            $clientId = $client->id;
                        }

                        \App\Models\Appointment::create([
                            'client_id' => $clientId,
                            'service' => $serviceName,
                            'start_time' => $start,
                            'duration' => $duration,
                            'status' => 'confirmed',
                            'attendance_status' => 'pending',
                            'event_id' => $event->id,
                        ]);

                        $syncResults['created']++;
                    } else {
                        $needsUpdate = false;
                        $updateData = [];

                        if ($appointment->start_time->format('Y-m-d H:i:s') !== $start->format('Y-m-d H:i:s')) {
                            $updateData['start_time'] = $start;
                            $needsUpdate = true;
                        }

                        if ($appointment->duration != $duration) {
                            $updateData['duration'] = $duration;
                            $needsUpdate = true;
                        }

                        if ($appointment->service !== $serviceName) {
                            $updateData['service'] = $serviceName;
                            $needsUpdate = true;
                        }

                        if ($clientName) {
                            $currentClient = $appointment->client;
                            if (!$currentClient || strtolower($currentClient->name) !== strtolower($clientName)) {
                                $client = \App\Models\Client::whereRaw('LOWER(name) = LOWER(?)', [$clientName])->first();

                                if (!$client) {
                                    $client = \App\Models\Client::create([
                                        'name' => $clientName,
                                        'phone' => $phone,
                                        'status' => 'Green'
                                    ]);
                                }

                                $updateData['client_id'] = $client->id;
                                $needsUpdate = true;
                            }
                        } else {
                            if ($appointment->client_id !== null) {
                                $updateData['client_id'] = null;
                                $needsUpdate = true;
                            }
                        }

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

            // ---- Return Sync Results ----
            if (!empty($syncResults['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync completed with some errors',
                    'results' => $syncResults
                ]);
            }

            return response()->json([
                'success' => true,
                'message' => 'Calendar sync completed successfully',
                'results' => $syncResults
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Sync failed: ' . $e->getMessage(),
                'results' => ['created' => 0, 'updated' => 0, 'errors' => [$e->getMessage()]]
            ]);
        }
    }


    public function create()
    {
        return Inertia::render('appointment/create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'client_number'     => 'nullable|string',
            'new_client_name'   => 'nullable|string',
            'new_client_phone'  => 'nullable|string',
            'email'             => 'nullable|email',
            'service'           => 'required|in:Hair Cut,Beard Shaping,Other Services',
            'start_time'        => 'required|date',
            'duration'          => 'required|integer|min:5',
            'status'            => 'required|in:Scheduled,Confirmed,Canceled',
            'notes'             => 'nullable|string',
        ]);

        // normalize start/end
        $start = Carbon::parse($request->start_time);
        $end = (clone $start)->addMinutes((int) $request->duration);

        // find or create client
        $client = Client::firstOrCreate(
            ['phone' => $request->client_number ?? $request->new_client_phone],
            ['name' => $request->new_client_name, 'email' => $request->email]
        );

        $event = Event::create([
            'name' => $client->name . ' - ' . $request->service,
            'startDateTime' => $start,
            'endDateTime'   => $end,
        ]);

        // save it in your DB
        $appointment = new Appointment();
        $appointment->event_id   = $event->id;
        $appointment->client_id  = $client->id;
        $appointment->service    = $request->service;
        $appointment->duration   = $request->duration;
        $appointment->start_time = $start;
        $appointment->status     = $request->status;
        $appointment->notes      = $request->notes;
        $appointment->save();

        return redirect()->back()->with('success', 'Appointment created successfully!');
    }

    public function update(Request $request, Appointment $appointment)
    {
        $request->validate([
            'client_id'         => ['nullable', 'integer', 'exists:clients,id'],
            'service'           => ['required', 'string', 'max:255'],
            'duration'          => ['nullable', 'numeric', 'max:120'],
            'attendance_status' => ['nullable', 'string', 'max:50'],
            'start_time'        => ['required', 'date'],
            'status'            => ['required', 'string', 'max:50'],
            'reminder_sent'     => ['nullable', 'date'],
            'notes'             => ['nullable', 'string'],

            'event_id'          => ['required', 'string'],
            'client_name'       => ['nullable', 'string'],
            'client_phone'      => ['required', 'string'],
            'client_email'      => ['nullable', 'email'],
        ]);

        // Update the existing client if client_id is provided, otherwise find by phone or create new
        if ($request->filled('client_id')) {
            // Use the existing client from the appointment
            $client = Client::findOrFail($request->client_id);
            $client->update([
                'name'  => $request->client_name,
                'phone' => $request->client_phone,
                'email' => $request->client_email,
            ]);
        } else {
            // Find by phone or create new client (fallback for appointments without client_id)
            $client = Client::where('phone', $request->client_phone)->first();

            if ($client) {
                $client->update([
                    'name'  => $request->client_name,
                    'phone' => $request->client_phone,
                    'email' => $request->client_email,
                ]);
            } else {
                $client = Client::create([
                    'name'  => $request->client_name,
                    'phone' => $request->client_phone,
                    'email' => $request->client_email,
                ]);
            }
        }

        // update appointment
        $appointment->update([
            'client_id'         => $client->id,
            'service'           => $request->service,
            'duration'          => $request->duration,
            'attendance_status' => $request->attendance_status,
            'start_time'        => $request->start_time,
            'status'            => $request->status,
            'reminder_sent'     => $request->reminder_sent,
            'notes'             => $request->notes,
            'event_id'          => $request->event_id,
        ]);

        // update related event if exists
        if ($appointment->event_id) {
            $event = Event::find($appointment->event_id);

            if ($event) {
                $event->name = $client->name . ' - ' . $appointment->service;

                $start = Carbon::parse($appointment->start_time);
                $duration = (int) ($appointment->duration ?? 30);

                $event->startDateTime = $start;
                $event->endDateTime   = (clone $start)->addMinutes($duration);

                $event->save();
            }
        }

        return back(303)->with('success', 'Appointment updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {


        $appointment = Appointment::findOrFail($id);

        if ($appointment->event_id) {
            try {
                $googleEvent = Event::find($appointment->event_id);
                $googleEvent?->delete();
            } catch (GoogleServiceException $e) {
                // Ignore if Google says event doesn't exist anymore
                if (in_array($e->getCode(), [404, 410])) {
                    Log::info("Google event already deleted: {$appointment->event_id}");
                } else {
                    throw $e; // rethrow unexpected errors
                }
            } catch (\Exception $e) {
                // Log other exceptions, but don't block deletion
                Log::warning("Failed to delete Google event {$appointment->event_id}: {$e->getMessage()}");
            }
        }

        // Delete the appointment locally
        $appointment->delete();

        // If you’re using Inertia + Ziggy:
        return redirect()
            ->back()
            ->with('success', 'Appointment deleted successfully.');
    }

    public function updateTime(Request $request)
    {
        $request->validate([
            'id' => 'required|exists:appointments,id',
            'start_time' => 'required|date',
            'duration' => 'required|integer|min:1',
        ]);

        $appointment = Appointment::findOrFail($request->id);
        $appointment->start_time = $request->start_time;
        $appointment->duration = $request->duration;
        $appointment->save();

        return response()->json(['success' => true]);
    }
}
