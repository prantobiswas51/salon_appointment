<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClientController extends Controller
{
    public function index(Request $request)
    {
        $query = Appointment::query();

        // 🔍 Search functionality
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('client_name', 'LIKE', "%{$search}%")
                    ->orWhere('client_phone', 'LIKE', "%{$search}%")
                    ->orWhere('service', 'LIKE', "%{$search}%")
                    ->orWhere('notes', 'LIKE', "%{$search}%")
                    ->orWhere('status', 'LIKE', "%{$search}%");
            });
        }

        // 📅 Date range filter
        if ($request->filled('date_from')) {
            $query->whereDate('start_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('start_time', '<=', $request->date_to);
        }

        // ⚙️ Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 💇 Service filter
        if ($request->filled('service')) {
            $query->where('service', 'LIKE', "%{$request->service}%");
        }

        // 🧾 Attendance filter (if exists)
        if ($request->filled('attendance_status')) {
            $query->where('attendance_status', $request->attendance_status);
        }

        // 🗂️ Paginate and keep filters
        $appointments = $query->latest()->paginate(10);
        $appointments->appends($request->only(['q', 'date_from', 'date_to', 'status', 'service', 'attendance_status']));

        return Inertia::render('client/index', [
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

    public function create()
    {
        return Inertia::render('client/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_name' => 'nullable|string',
            'client_phone' => 'nullable|string',
            'email' => 'nullable|email',
            'service' => 'required|in:Hair Cut,Beard Shaping,Other Services',
            'start_time' => 'required|date',
            'status' => 'required|in:Scheduled,Confirmed,Canceled',
            'notes' => 'nullable|string',
        ]);

        Appointment::create($validated);

        return redirect()->route('clients.index')->with('success', 'Appointment created successfully.');
    }
}
