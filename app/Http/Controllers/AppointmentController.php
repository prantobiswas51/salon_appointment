<?php

namespace App\Http\Controllers;

use App\Models\Client;
use App\Models\Appointment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class AppointmentController extends Controller
{
    public function create()
    {
        $clients = Client::all(); // Get all clients to populate the client dropdown
        return view('appointments.create', compact('clients'));
    }

    // Store a new appointment
    public function store_appointment(Request $request)
    {
        // Validate form data
        $validator = Validator::make($request->all(), [
            'client_id' => 'required|exists:clients,id',
            'service' => 'required|in:Hair Cut,Beard Shaping,Other Services',
            'appointment_time' => 'required|date',
            'duration' => 'nullable|integer',
            'attendance_status' => 'nullable|in:attended,canceled,no_show',
            'status' => 'nullable|in:Scheduled,Confirmed,Canceled',
            'reminder_sent' => 'nullable|boolean',
            'notes' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return redirect()->route('appointments.create')
                ->withErrors($validator)
                ->withInput();
        }

        // Create the new appointment
        $appointment = Appointment::create([
            'client_id' => $request->client_id,
            'service' => $request->service,
            'appointment_time' => $request->appointment_time,
            'duration' => $request->duration ?? 60,
            'attendance_status' => $request->attendance_status,
            'status' => $request->status ?? 'Scheduled',
            'reminder_sent' => $request->reminder_sent ?? false,
            'notes' => $request->notes,
        ]);

        return redirect()->route('appointments.show', $appointment->id)
            ->with('success', 'Appointment booked successfully');
    }

    // Show the details of an appointment
    public function show($id)
    {
        $appointment = Appointment::findOrFail($id);
        return view('appointments.show', compact('appointment'));
    }
}
