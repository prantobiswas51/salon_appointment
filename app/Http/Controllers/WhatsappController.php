<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use Inertia\Inertia;
use App\Models\Reminder;
use App\Models\Whatsapp;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Http;

class WhatsappController extends Controller
{
    public function index()
    {
        $whatsapps = Whatsapp::all();

        return Inertia::render('whatsapp/index', [
            'whatsapps' => $whatsapps,
        ]);
    }

    public function update(Whatsapp $whatsapp)
    {
        request()->validate([
            'message' => 'required|string',
            'token' => 'required|string',
            'number_id' => 'required|string',
        ]);

        $whatsapp->update(request()->only('message', 'token', 'number_id'));

        return redirect()->back()->with('success', 'Whatsapp updated!');
    }
}
