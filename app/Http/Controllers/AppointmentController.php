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

        // fetch Google Calendar events (already synced into GoogleEvent model)
        $googleEvents = GoogleEvent::get();

        $events = $googleEvents->map(function ($event) {
            // Try to find an existing appointment with this event_id
            $appointment = Appointment::where('event_id', $event->id)->first();

            $start = Carbon::parse($event->startDateTime)->timezone('UTC');
            $end   = Carbon::parse($event->endDateTime)->timezone('UTC');
            $duration = $start->diffInMinutes($end);

            if (!$appointment) {
                // Create new appointment if not exists
                Appointment::create([
                    'client_id'         => null, // you’ll need logic to assign client if required
                    'service'           => $event->name,
                    'start_time'        => $start,
                    'duration'          => $duration,
                    'notes'             => null,
                    'event_id'          => $event->id, // keep track to avoid duplicates
                ]);
            }
        });
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

        // find or create client by phone
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
