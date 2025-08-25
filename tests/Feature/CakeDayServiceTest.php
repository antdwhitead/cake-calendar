<?php

declare(strict_types=1);

use App\Enums\CakeType;
use App\Models\CakeDay;
use App\Models\User;
use App\Services\CakeDayService;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;

uses(RefreshDatabase::class);

beforeEach(/**
 * @throws ReflectionException
 */ function () {
    $this->service = new CakeDayService(2025);
    $this->reflection = new ReflectionClass($this->service);

    // Make protected methods accessible
    $protectedMethods = [
        'storeUsers',
        'calculateCakeDays',
        'generateInitialCakeDays',
        'getNextWorkingDay',
        'isNonWorkingDay',
        'applyCakeRules',
        'postponeCakeDay',
        'hasCakeOnConsecutiveDays',
        'storeCakeDays',
        'parseFile',
    ];

    foreach ($protectedMethods as $methodName) {
        if ($this->reflection->hasMethod($methodName)) {
            $method = $this->reflection->getMethod($methodName);
            $this->{$methodName.'Method'} = $method;
        }
    }
});

it('can determine if a date is a non-working day', function () {
    // Weekend
    expect($this->isNonWorkingDayMethod->invoke($this->service, Carbon::create(2025, 6, 14)))->toBeTrue() // Saturday
        ->and($this->isNonWorkingDayMethod->invoke($this->service, Carbon::create(2025, 6, 15)))->toBeTrue() // Sunday
        // Holidays
        ->and($this->isNonWorkingDayMethod->invoke($this->service, Carbon::create(2025, 12, 25)))->toBeTrue() // Christmas
        ->and($this->isNonWorkingDayMethod->invoke($this->service, Carbon::create(2025, 12, 26)))->toBeTrue() // Boxing Day
        ->and($this->isNonWorkingDayMethod->invoke($this->service, Carbon::create(2025, 1, 1)))->toBeTrue() // New Year's
        // Working day
        ->and($this->isNonWorkingDayMethod->invoke($this->service, Carbon::create(2025, 6, 16)))->toBeFalse(); // Monday
});

it('can get next working day after a date', function () {
    // Birthday on Friday, next working day is Monday
    $friday = Carbon::create(2025, 6, 13);
    $nextWorking = $this->getNextWorkingDayMethod->invoke($this->service, $friday);
    expect($nextWorking->format('Y-m-d'))->toBe('2025-06-16')
        // Birthday on Christmas, next working day skips Boxing Day too
        ->and($this->getNextWorkingDayMethod->invoke($this->service, Carbon::create(2025, 12, 25))->format('Y-m-d'))->toBe('2025-12-29'); // Monday after Boxing Day
});

it('can parse CSV file content', function () {
    // Create a real temporary CSV file with proper line endings
    $csvContent = "John Doe,1990-06-15\r\nJane Smith,1985-12-25\r\n";
    $tempFile = tempnam(sys_get_temp_dir(), 'test').'.csv';
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'employees.csv', 'text/csv', null, true);

    $users = $this->parseFileMethod->invoke($this->service, $file);

    expect($users)->toHaveCount(2)
        ->and($users->first()['name'])->toBe('John Doe')
        ->and($users->first()['date_of_birth'])->toBe('1990-06-15')
        ->and($users->last()['name'])->toBe('Jane Smith')
        ->and($users->last()['date_of_birth'])->toBe('1985-12-25');

    unlink($tempFile);
});

it('throws exception for unsupported file format', function () {
    $file = UploadedFile::fake()->create('test.pdf');

    expect(fn () => $this->parseFileMethod->invoke($this->service, $file))
        ->toThrow(InvalidArgumentException::class, 'Unsupported file format: pdf');
});

it('stores users without duplicates', function () {
    User::factory()->create([
        'name' => 'John Doe',
        'date_of_birth' => '1990-06-15',
    ]);

    $newUsers = collect([
        ['name' => 'John Doe', 'date_of_birth' => '1990-06-15'], // Duplicate
        ['name' => 'Jane Smith', 'date_of_birth' => '1985-12-25'], // New
    ]);

    // Set users property and call storeUsers method
    $property = $this->reflection->getProperty('users');
    $property->setValue($this->service, $newUsers);

    $this->storeUsersMethod->invoke($this->service);

    expect(User::count())->toBe(2)
        ->and(User::where('name', 'John Doe')->count())->toBe(1)
        ->and(User::where('name', 'Jane Smith')->count())->toBe(1);
});

it('calculates cake days with simple birthday scenario', function () {
    // Dave's birthday June 13th (Friday), cake day should be Monday 16th
    User::factory()->create([
        'name' => 'Dave',
        'date_of_birth' => '1986-06-13',
    ]);

    $cakeDays = $this->calculateCakeDaysMethod->invoke($this->service);

    expect($cakeDays)->toHaveCount(1)
        ->and($cakeDays->first()['date']->format('Y-m-d'))->toBe('2025-06-16') // Monday
        ->and($cakeDays->first()['cake_type'])->toBe(CakeType::Small)
        ->and($cakeDays->first()['names'])->toBe(['Dave']);
});

it('calculates large cake for coinciding birthdays', function () {
    // Sam and Kate have consecutive birthdays
    User::factory()->create(['name' => 'Sam', 'date_of_birth' => '1990-07-14']);
    User::factory()->create(['name' => 'Kate', 'date_of_birth' => '1992-07-15']);

    $cakeDays = $this->calculateCakeDaysMethod->invoke($this->service);

    expect($cakeDays)->toHaveCount(1)
        ->and($cakeDays->first()['date']->format('Y-m-d'))->toBe('2025-07-16') // Wednesday
        ->and($cakeDays->first()['cake_type'])->toBe(CakeType::Large)
        ->and($cakeDays->first()['names'])->toContain('Sam')
        ->and($cakeDays->first()['names'])->toContain('Kate');
});

it('handles birthday on holiday with day off', function () {
    // Rob's birthday July 6th (Sunday), gets Monday off, cake on Tuesday
    User::factory()->create([
        'name' => 'Rob',
        'date_of_birth' => '1950-07-06',
    ]);

    $cakeDays = $this->calculateCakeDaysMethod->invoke($this->service);

    expect($cakeDays)->toHaveCount(1)
        ->and($cakeDays->first()['date']->format('Y-m-d'))->toBe('2025-07-08') // Tuesday
        ->and($cakeDays->first()['cake_type'])->toBe(CakeType::Small);
});

it('enforces cake-free day rule with postponement', function () {
    // Create scenario where cake days would be consecutive
    User::factory()->create(['name' => 'Alex', 'date_of_birth' => '1990-07-21']);
    User::factory()->create(['name' => 'Jen', 'date_of_birth' => '1991-07-22']);
    User::factory()->create(['name' => 'Pete', 'date_of_birth' => '1992-07-23']);

    $cakeDays = $this->calculateCakeDaysMethod->invoke($this->service);

    expect($cakeDays)->toHaveCount(2)
        // First cake day - Alex and Jen share large cake
        ->and($cakeDays->first()['date']->format('Y-m-d'))->toBe('2025-07-23') // Wednesday
        ->and($cakeDays->first()['cake_type'])->toBe(CakeType::Large)
        ->and($cakeDays->first()['names'])->toContain('Alex')
        ->and($cakeDays->first()['names'])->toContain('Jen')
        // Second cake day - Pete gets postponed to Friday (Thursday is cake-free)
        ->and($cakeDays->last()['date']->format('Y-m-d'))->toBe('2025-07-25') // Friday
        ->and($cakeDays->last()['cake_type'])->toBe(CakeType::Small)
        ->and($cakeDays->last()['names'])->toBe(['Pete']);
});

it('can store calculated cake days to database', function () {
    $cakeDays = collect([
        [
            'date' => Carbon::create(2025, 6, 16),
            'cake_type' => CakeType::Small,
            'names' => ['Dave'],
        ],
        [
            'date' => Carbon::create(2025, 7, 16),
            'cake_type' => CakeType::Large,
            'names' => ['Sam', 'Kate'],
        ],
    ]);

    // Use reflection to set finalCakeDays and call storeCakeDays
    $property = $this->reflection->getProperty('finalCakeDays');
    $property->setValue($this->service, $cakeDays);

    $this->storeCakeDaysMethod->invoke($this->service);

    expect(CakeDay::count())->toBe(2)
        ->and(CakeDay::where('date', '2025-06-16')->first()->cake_type)->toBe(CakeType::Small)
        ->and(CakeDay::where('date', '2025-06-16')->first()->names)->toBe(['Dave'])
        ->and(CakeDay::where('date', '2025-07-16')->first()->cake_type)->toBe(CakeType::Large)
        ->and(CakeDay::where('date', '2025-07-16')->first()->names)->toBe(['Sam', 'Kate']);
});

it('can retrieve cake days for specific year', function () {
    CakeDay::factory()->create(['date' => '2025-06-16']);
    CakeDay::factory()->create(['date' => '2024-07-15']); // Different year

    $cakeDays = $this->service->getCakeDaysForYear();

    expect($cakeDays)->toHaveCount(1)
        ->and($cakeDays->first()->date->format('Y'))->toBe('2025');
});

it('processes complete file upload workflow', function () {
    // Create a real temporary CSV file with proper line endings
    $csvContent = "John Doe,1990-06-13\r\nJane Smith,1985-07-14\r\n";
    $tempFile = tempnam(sys_get_temp_dir(), 'test').'.csv';
    file_put_contents($tempFile, $csvContent);

    $file = new UploadedFile($tempFile, 'employees.csv', 'text/csv', null, true);

    $this->service->processFileUpload($file);

    // Check users were stored
    expect(User::count())->toBe(2)
        // Check cake days were stored
        ->and(CakeDay::count())->toBe(2);

    unlink($tempFile);
});
