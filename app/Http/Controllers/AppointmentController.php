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
            // Use EventParserService to sync events with proper parsing and update handling
            // This will automatically try to fetch fresh events from Google Calendar
            $syncResults = EventParserService::syncGoogleCalendarEvents();

            // Check if sync was successful
            if (!empty($syncResults['errors'])) {
                return response()->json([
                    'success' => false,
                    'message' => 'Sync completed with errors',
                    'results' => $syncResults
                ]);
            }

            // Return JSON response with sync results
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
