<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Appointment;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        $today = now()->toDateString();

        $appointments = Appointment::with('client')
            ->whereDate('start_time', $today)
            ->orderBy('start_time')
            ->get();

        // Current in-progress appointment
        $inProgress = Appointment::with('client')
            ->where('status', 'in_progress')
            ->first();

        // Next appointment (not started yet, after now)
        $nextAppointment = Appointment::with('client')
            ->where('start_time', '>', now())
            ->where('status', 'scheduled')
            ->orderBy('start_time')
            ->first();

        return inertia('Dashboard', [
            'appointments' => $appointments->map(fn($a) => [
                'id' => $a->id,
                'client_name' => $a->client->name,
                'service' => $a->service,
                'start_time' => $a->start_time->format('H:i'),
                'duration' => $a->duration,
                'status' => $a->status,
            ]),
            'inProgress' => $inProgress ? [
                'id' => $inProgress->id,
                'client_name' => $inProgress->client->name,
                'service' => $inProgress->service,
                'start_time' => $inProgress->start_time->format('H:i'),
                'duration' => $inProgress->duration,
                'status' => $inProgress->status,
            ] : null,
            'nextAppointment' => $nextAppointment ? [
                'id' => $nextAppointment->id,
                'client_name' => $nextAppointment->client->name,
                'service' => $nextAppointment->service,
                'start_time' => $nextAppointment->start_time->format('H:i'),
                'duration' => $nextAppointment->duration,
                'status' => $nextAppointment->status,
            ] : null,
            'countToday' => $appointments->count(),
            'countWeek' => Appointment::whereBetween('start_time', [now()->startOfWeek(), now()->endOfWeek()])->count(),
            'countMonth' => Appointment::whereMonth('start_time', now()->month)->count(),
        ]);
    }
}
