<?php

declare(strict_types=1);

use App\Enums\CakeType;
use App\Models\CakeDay;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

it('displays home page with current year cake days', function () {
    $currentYear = now()->year;

    // Create some test cake days for current year
    $cakeDay1 = CakeDay::factory()->create([
        'date' => $currentYear.'-03-15',
        'cake_type' => CakeType::Small,
        'names' => ['John Doe'],
    ]);

    $cakeDay2 = CakeDay::factory()->create([
        'date' => $currentYear.'-06-20',
        'cake_type' => CakeType::Large,
        'names' => ['Jane Smith', 'Bob Wilson'],
    ]);

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page->component('Home')
        ->has('cakeDays', 2)
        ->where('currentYear', $currentYear)
        ->has('cakeDays.0', fn (Assert $cakeDay) => $cakeDay->where('id', $cakeDay1->id)
            ->where('date', $currentYear.'-03-15')
            ->where('cakeType', 'small')
            ->where('names', ['John Doe'])
        )
        ->has('cakeDays.1', fn (Assert $cakeDay) => $cakeDay->where('id', $cakeDay2->id)
            ->where('date', $currentYear.'-06-20')
            ->where('cakeType', 'large')
            ->where('names', ['Jane Smith', 'Bob Wilson'])
        )
    );
});

it('displays home page with specific year cake days', function () {
    $targetYear = 2023;

    // Create cake days for different years
    $cakeDay2023 = CakeDay::factory()->create([
        'date' => '2023-04-10',
        'cake_type' => CakeType::Small,
        'names' => ['Alice Johnson'],
    ]);

    CakeDay::factory()->create([
        'date' => '2024-04-10',
        'cake_type' => CakeType::Small,
        'names' => ['Bob Smith'],
    ]);

    $response = $this->get(route('home', ['year' => $targetYear]));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page->component('Home')
        ->has('cakeDays', 1)
        ->where('currentYear', $targetYear)
        ->has('cakeDays.0', fn (Assert $cakeDay) => $cakeDay->where('id', $cakeDay2023->id)
            ->where('date', '2023-04-10')
            ->where('cakeType', 'small')
            ->where('names', ['Alice Johnson'])
        )
    );
});

it('displays empty cake days when no data exists', function () {
    $currentYear = now()->year;

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page->component('Home')
        ->has('cakeDays', 0)
        ->where('currentYear', $currentYear)
    );
});

it('handles invalid year parameter gracefully', function () {
    // Test with non-numeric year parameter - Laravel's integer() returns 0 for invalid input
    $response = $this->get(route('home', ['year' => 'invalid']));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page->component('Home')
        ->where('currentYear', 0)
    );
});

it('sorts cake days by date', function () {
    $currentYear = now()->year;

    // Create cake days in non-chronological order
    $laterCakeDay = CakeDay::factory()->create([
        'date' => $currentYear.'-12-01',
        'cake_type' => CakeType::Small,
        'names' => ['Late Birthday'],
    ]);

    $earlierCakeDay = CakeDay::factory()->create([
        'date' => $currentYear.'-01-15',
        'cake_type' => CakeType::Small,
        'names' => ['Early Birthday'],
    ]);

    $response = $this->get(route('home'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page->component('Home')
        ->has('cakeDays', 2)
        ->has('cakeDays.0', fn (Assert $cakeDay) => $cakeDay->where('id', $earlierCakeDay->id)
            ->where('date', $currentYear.'-01-15')
            ->where('cakeType', 'small')
            ->where('names', ['Early Birthday'])
        )
        ->has('cakeDays.1', fn (Assert $cakeDay) => $cakeDay->where('id', $laterCakeDay->id)
            ->where('date', $currentYear.'-12-01')
            ->where('cakeType', 'small')
            ->where('names', ['Late Birthday'])
        )
    );
});
