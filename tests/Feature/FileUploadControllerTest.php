<?php

declare(strict_types=1);

use App\Enums\CakeType;
use App\Models\CakeDay;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Inertia\Testing\AssertableInertia as Assert;

uses(RefreshDatabase::class);

beforeEach(function () {
    Storage::fake('local');
});

it('displays the upload page', function () {
    $response = $this->get(route('upload.show'));

    $response->assertStatus(200);
    $response->assertInertia(fn (Assert $page) => $page->component('Upload')
    );
});

it('successfully uploads and processes a CSV file', function () {
    $csvContent = "John Doe,1990-03-15\nJane Smith,1985-06-20";
    $file = UploadedFile::fake()->createWithContent('employees.csv', $csvContent);

    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 2024,
    ]);

    $response->assertRedirect(route('home', ['year' => 2024]));
    $response->assertSessionHas('success', 'File uploaded and processed successfully!');

    // Verify users were created
    $this->assertDatabaseHas('users', [
        'name' => 'John Doe',
        'date_of_birth' => '1990-03-15',
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Jane Smith',
        'date_of_birth' => '1985-06-20',
    ]);

    // Verify cake days were created
    $this->assertDatabaseCount('cake_days', 2);
});

it('successfully uploads and processes a TXT file', function () {
    $txtContent = "Alice Johnson,1992-01-10\nBob Wilson,1988-11-25";
    $file = UploadedFile::fake()->createWithContent('employees.txt', $txtContent);

    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 2023,
    ]);

    $response->assertRedirect(route('home', ['year' => 2023]));
    $response->assertSessionHas('success', 'File uploaded and processed successfully!');

    // Verify users were created
    $this->assertDatabaseHas('users', [
        'name' => 'Alice Johnson',
        'date_of_birth' => '1992-01-10',
    ]);

    $this->assertDatabaseHas('users', [
        'name' => 'Bob Wilson',
        'date_of_birth' => '1988-11-25',
    ]);
});

it('uses current year when year parameter is not provided', function () {
    $currentYear = now()->year;
    $csvContent = 'Test User,1990-05-15';
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    $response = $this->post(route('upload.store'), [
        'file' => $file,
    ]);

    $response->assertRedirect(route('home', ['year' => $currentYear]));
    $response->assertSessionHas('success', 'File uploaded and processed successfully!');
});

it('validates file is required', function () {
    $response = $this->post(route('upload.store'), [
        'year' => 2024,
    ]);

    $response->assertSessionHasErrors('file');
});

it('validates file type restrictions', function () {
    $invalidFile = UploadedFile::fake()->create('test.pdf', 100);

    $response = $this->post(route('upload.store'), [
        'file' => $invalidFile,
        'year' => 2024,
    ]);

    $response->assertSessionHasErrors('file');
});

it('validates file size limit', function () {
    // Create a file larger than 2MB (2048 KB)
    $largeFile = UploadedFile::fake()->create('large.csv', 3000);

    $response = $this->post(route('upload.store'), [
        'file' => $largeFile,
        'year' => 2024,
    ]);

    $response->assertSessionHasErrors('file');
});

it('validates year parameter constraints', function () {
    $csvContent = 'Test User,1990-05-15';
    $file = UploadedFile::fake()->createWithContent('test.csv', $csvContent);

    // Test year too low
    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 1999,
    ]);

    $response->assertSessionHasErrors('year');

    // Test year too high
    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 2101,
    ]);

    $response->assertSessionHasErrors('year');

    // Test non-integer year
    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 'invalid',
    ]);

    $response->assertSessionHasErrors('year');
});

it('processes file with multiple users and creates appropriate cake days', function () {
    // Create a CSV with multiple users having birthdays that would test the cake day logic
    $csvContent = "User One,1990-03-15\nUser Two,1985-03-15\nUser Three,1992-06-20";
    $file = UploadedFile::fake()->createWithContent('multiple_users.csv', $csvContent);

    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 2024,
    ]);

    $response->assertRedirect(route('home', ['year' => 2024]));

    // Verify all users were created
    $this->assertDatabaseHas('users', ['name' => 'User One']);
    $this->assertDatabaseHas('users', ['name' => 'User Two']);
    $this->assertDatabaseHas('users', ['name' => 'User Three']);

    // Verify cake days were created
    $this->assertDatabaseCount('cake_days', 2); // Two cake days should be created based on service logic
});

it('clears existing cake days when processing new file', function () {
    // Create some existing cake days
    CakeDay::factory()->create([
        'date' => '2024-01-01',
        'cake_type' => CakeType::Small,
        'names' => ['Old User'],
    ]);

    $this->assertDatabaseCount('cake_days', 1);

    // Upload new file
    $csvContent = 'New User,1990-05-15';
    $file = UploadedFile::fake()->createWithContent('new_data.csv', $csvContent);

    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 2024,
    ]);

    $response->assertRedirect(route('home', ['year' => 2024]));

    // Old cake days should be cleared and new ones created
    $this->assertDatabaseCount('cake_days', 1);
    $this->assertDatabaseMissing('cake_days', ['names' => ['Old User']]);
});

it('handles empty file gracefully', function () {
    $emptyFile = UploadedFile::fake()->createWithContent('empty.csv', '');

    $response = $this->post(route('upload.store'), [
        'file' => $emptyFile,
        'year' => 2024,
    ]);

    $response->assertRedirect(route('home', ['year' => 2024]));
    $response->assertSessionHas('success', 'File uploaded and processed successfully!');

    // No users or cake days should be created
    $this->assertDatabaseCount('users', 0);
    $this->assertDatabaseCount('cake_days', 0);
});

it('handles malformed CSV data gracefully', function () {
    // Mix of valid and invalid rows - some missing data, some with only one column
    $malformedContent = "John Doe\nJane Smith,1985-06-20\n,1990-01-01\nBob Wilson,";
    $file = UploadedFile::fake()->createWithContent('malformed.csv', $malformedContent);

    $response = $this->post(route('upload.store'), [
        'file' => $file,
        'year' => 2024,
    ]);

    $response->assertRedirect(route('home', ['year' => 2024]));
    $response->assertSessionHas('success', 'File uploaded and processed successfully!');

    // Only valid entries should be processed
    $this->assertDatabaseHas('users', [
        'name' => 'Jane Smith',
        'date_of_birth' => '1985-06-20',
    ]);

    // Invalid entries should be skipped - only 1 user should be created
    $this->assertDatabaseCount('users', 1);
    $this->assertDatabaseMissing('users', ['name' => 'John Doe']);
    $this->assertDatabaseMissing('users', ['name' => 'Bob Wilson']);
    $this->assertDatabaseMissing('users', ['name' => '']);
});
