<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

use function Pest\Laravel\json;
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
}
