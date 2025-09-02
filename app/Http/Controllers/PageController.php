<?php

namespace App\Http\Controllers;

use Inertia\Inertia;
use Illuminate\Http\Request;

class PageController extends Controller
{
    public function privacy_policy()
    {
        return Inertia::render('privacy');
    }

    public function terms_condition()
    {
        return Inertia::render('terms_condition');
    }
}
