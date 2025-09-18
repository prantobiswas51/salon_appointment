<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Spatie\GoogleCalendar\Event as GoogleEvent;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use function Pest\Laravel\json;

use Spatie\GoogleCalendar\Event;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\returnSelf;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('client')
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('appointment/index', [
            'appointments' => $appointments,
        ]);
    }

    public function events()
    {
        // fetch Google Calendar events
        $googleEvents = GoogleEvent::get();

        // map Google events to FullCalendar format
        $events = $googleEvents->map(function ($event) {
            return [
                'title' => $event->name,
                'start' => Carbon::parse($event->startDateTime)->toIso8601String(),
                'end'   => Carbon::parse($event->endDateTime)->toIso8601String(),
                'backgroundColor' => '#ef4444',
                'borderColor' => '#b91c1c',
            ];
        });

        return response()->json($events);
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
        $validated = $request->validate([
            'client_id'         => ['required', 'integer', 'exists:clients,id'],
            'service'           => ['required', 'string', 'max:255'],
            'duration'          => ['nullable', 'numeric', 'max:120'],
            'attendance_status' => ['nullable', 'string', 'max:50'],
            'start_time'        => ['required', 'date'],
            'status'            => ['required', 'string', 'max:50'],
            'reminder_sent'     => ['nullable', 'date'],
            'notes'             => ['nullable', 'string'],
        ]);

        // Update appointment locally
        $appointment->update($validated);

        if ($appointment->event_id) {
            $event = Event::find($appointment->event_id);

            if ($event) {
                $event->name = $appointment->client->name . ' - ' . $appointment->service;
                $event->startDateTime = $appointment->start_time;

                $duration = (int) ($appointment->duration ?? 30);
                $event->endDateTime = (clone $appointment->start_time)->addMinutes($duration);

                $event->save();
            }
        }


        return back(303)->with('success', 'Appointment updated successfully.');
    }
    public function destroy(int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        // Delete the Google Calendar event first
        if ($appointment->event_id) {
            $event = Event::find($appointment->event_id); // only works if $event_id is Spatie Event::id
            // If $appointment->event_id is Google Event ID, do:
            $googleEvent = Event::find($appointment->event_id);
            $googleEvent?->delete();
        }

        // Delete the appointment locally
        $appointment->delete();

        // If you’re using Inertia + Ziggy:
        return redirect()
            ->back()
            ->with('success', 'Appointment deleted successfully.');
    }
}
