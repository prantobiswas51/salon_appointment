<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

use function Pest\Laravel\json;
use Illuminate\Support\Facades\Validator;
use function PHPUnit\Framework\returnSelf;

class AppointmentController extends Controller
{
    public function index()
    {
        $appointments = Appointment::with('client') // optional if you want client names
            // ->where('status', 'Scheduled')
            ->orderByDesc('created_at')
            ->paginate(10);

        return Inertia::render('appointment/index', [
            'appointments' => $appointments,
        ]);
    }

    public function create()
    {
        return Inertia::render('appointment/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_number' => 'nullable|string',
            'new_client_name' => 'nullable|string',
            'new_client_phone' => 'nullable|string',
            'email' => 'nullable|email',
            'service' => 'required|in:Hair Cut,Beard Shaping,Other Services',
            'appointment_time' => 'required|date',
            'status' => 'required|in:Scheduled,Confirmed,Canceled',
            'notes' => 'nullable|string',
        ]);

        try {
            if ($request->client_number) {

                $client = Client::where('phone', $request->client_number)->firstOrFail();

                $appointment = new Appointment();
                $appointment->client_id = $client->id;
                $appointment->service = $request->service;
                $appointment->duration = "30";
                $appointment->appointment_time = $request->appointment_time;
                $appointment->notes = $request->notes;
                $appointment->status = $request->status;
                $appointment->save();
            }

            if ($request->new_client_phone) {
                $client = new Client();
                $client->name = $request->new_client_name;
                $client->phone = $request->new_client_phone;
                $client->email = $request->email;
                $client->save();

                $appointment = new Appointment();
                $appointment->client_id = $client->id;
                $appointment->service = $request->service;
                $appointment->duration = "30";
                $appointment->appointment_time = $request->appointment_time;
                $appointment->notes = $request->notes;
                $appointment->status = $request->status;
                $appointment->save();
            }

            return redirect()->back()->with('success', 'Created Successfully');
        } catch (\Throwable $th) {
            return redirect()->back()->with('error', 'Something went wrong: ' . $th->getMessage());
        }
    }

    public function update(Request $request, Appointment $appointment)
    {
        // validate incoming data (tweak as needed)
        $validated = $request->validate([
            'client_id'          => ['required', 'integer', 'exists:clients,id'],
            'service'            => ['required', 'string', 'max:255'],
            'duration'           => ['nullable', 'string', 'max:50'],
            'attendence_status'  => ['nullable', 'string', 'max:50'],
            'appointment_time'   => ['required', 'date'],
            'status'             => ['required', 'string', 'max:50'],
            'notes'              => ['nullable', 'string'],
        ]);

        // If your frontend sends "datetime-local" (e.g., "2025-08-12T15:30"),
        // Laravel will usually parse it; but to be explicit:
        if (!empty($validated['appointment_time'])) {
            $validated['appointment_time'] = Carbon::parse($validated['appointment_time']);
        }

        $appointment->update($validated);

        // Inertia-friendly redirect with flash
        return back()->with('success', 'Appointment updated successfully.');
    }

    public function destroy(int $id): RedirectResponse
    {
        $appointment = Appointment::findOrFail($id);

        $appointment->delete();

        // If you’re using Inertia + Ziggy:
        return redirect()
            ->route('appointment.index')
            ->with('success', 'Appointment deleted successfully.');
    }
}
