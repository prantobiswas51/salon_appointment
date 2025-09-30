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
        $now   = now();
        $today = $now->toDateString();

        // Get all today's appointments (one query)
        $appointments = Appointment::with('client')
            ->whereDate('start_time', $today)
            ->orderBy('start_time')
            ->get();

        // Current in-progress appointment
        $inProgress = $appointments->firstWhere('status', 'in_progress');

        // Next appointment (scheduled, after now)
        $nextAppointment = $appointments
            ->where('status', 'scheduled')
            ->where('start_time', '>', $now)
            ->sortBy('start_time')
            ->first();

        // Map for dashboard table
        $appointmentsData = $appointments->map(fn($a) => [
            'id'          => $a->id,
            'client_name' => $a->client->name ?? "",
            'service'     => $a->service,
            'start_time'  => $a->start_time->format('H:i'),
            'duration'    => $a->duration,
            'status'      => $a->status,
        ]);

        // FullCalendar events (reuse same $appointments)
        $calendarEvents = $appointments->map(function ($event) {
            return [
                'title' => ($event->client->name ?? 'Unknown') . ' - ' . $event->service,
                'start' => $event->start_time->toIso8601String(),
                'end'   => $event->start_time->copy()->addMinutes($event->duration)->toIso8601String(),
                'backgroundColor' => match ($event->status) {
                    'in_progress' => '#3b82f6', // blue
                    'completed'   => '#22c55e', // green
                    'cancelled'   => '#ef4444', // red
                    default       => '#facc15', // yellow
                },
                'borderColor' => '#000000',
            ];
        });

        return inertia('Dashboard', [
            'appointments' => $appointmentsData,

            'inProgress' => $inProgress ? [
                'id'          => $inProgress->id,
                'client_name' => $inProgress->client->name ?? "",
                'service'     => $inProgress->service,
                'start_time'  => $inProgress->start_time->format('H:i'),
                'duration'    => $inProgress->duration,
                'status'      => $inProgress->status,
            ] : null,

            'nextAppointment' => $nextAppointment ? [
                'id'          => $nextAppointment->id,
                'client_name' => $nextAppointment->client->name ?? "",
                'service'     => $nextAppointment->service,
                'start_time'  => $nextAppointment->start_time->format('H:i'),
                'duration'    => $nextAppointment->duration,
                'status'      => $nextAppointment->status,
            ] : null,

            'countToday' => $appointments->count(),
            'countWeek'  => Appointment::whereBetween('start_time', [$now->startOfWeek(), $now->endOfWeek()])->count(),
            'countMonth' => Appointment::whereMonth('start_time', $now->month)->count(),

            'calendarEvents' => $calendarEvents,
        ]);
    }
}
