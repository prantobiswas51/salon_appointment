<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use App\Models\Client;
use Illuminate\Http\Request;

class ClientController extends Controller
{
    public function index()
    {
        $clients = Client::orderByDesc('created_at')->paginate(10);

        return Inertia::render('client/index', [
            'clients' => $clients,
        ]);
    }

    public function create()
    {
        return Inertia::render('client/create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'client_number' => 'nullable|string',
            'new_client_name' => 'nullable|string',
            'new_client_phone' => 'nullable|string',
            'email' => 'nullable|email',
            'service' => 'required|in:Hair Cut,Beard Shaping,Other Services',
            'start_time' => 'required|date',
            'status' => 'required|in:Scheduled,Confirmed,Canceled',
            'notes' => 'nullable|string',
        ]);
    }

    public function update(Request $request, $id)
    {
        $client = Client::findOrFail($id);

        $validated = $request->validate([
            'name' => 'nullable|string',
            'email' => 'nullable|email',
            'dob' => 'nullable|date',
            'phone' => 'nullable|string',
            'status' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        $client->update($validated);

        // Return the updated client data back to the front end (Inertia)
        return redirect()->route('client.index');
    }
}
