<?php

use App\Models\Appointment;
use App\Models\Client;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

it('can display appointments index page', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user)->get('/appointments');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('appointment/index'));
});

it('can search appointments by client name', function () {
    $user = User::factory()->create();
    $client = Client::factory()->create(['name' => 'John Doe']);
    $appointment = Appointment::factory()->create(['client_id' => $client->id]);

    $response = $this->actingAs($user)->get('/appointments?q=John');

    $response->assertSuccessful();
    $response->assertInertia(fn ($page) => $page->component('appointment/index')
        ->has('appointments.data', 1)
        ->where('filters.q', 'John')
    );
});
