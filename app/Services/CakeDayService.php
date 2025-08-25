<?php

declare(strict_types=1);

namespace App\Services;

use App\Enums\CakeType;
use App\Models\CakeDay;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Collection;
use Spatie\SimpleExcel\SimpleExcelReader;

class CakeDayService
{
    protected array $holidays = [
        'Christmas Day' => '12-25',
        'Boxing Day' => '12-26',
        'New Year\'s Day' => '01-01',
    ];

    protected int $year;

    protected Collection $users;

    protected Collection $cakeDays;

    protected Collection $cakeFreeDays;

    protected Collection $finalCakeDays;

    public function __construct(?int $year = null)
    {
        $this->year = $year ?? now()->year;
        $this->users = collect();
        $this->cakeDays = collect();
        $this->cakeFreeDays = collect();
        $this->finalCakeDays = collect();
    }

    public function processFileUpload(UploadedFile $file): void
    {
        $this->users = $this->parseFile($file);
        $this->storeUsers();
        $this->calculateCakeDays();
        $this->storeCakeDays();
    }

    /**
     * @throws \InvalidArgumentException
     */
    public function validateFile(UploadedFile $file): void
    {
        $extension = $file->getClientOriginalExtension();

        if (! in_array($extension, ['txt', 'csv', 'xlsx'])) {
            throw new \InvalidArgumentException("Unsupported file format: {$extension}");
        }
    }

    protected function parseFile(UploadedFile $file): Collection
    {
        $this->validateFile($file);

        return SimpleExcelReader::create($file->getRealPath())
            ->noHeaderRow()
            ->getRows()
            ->map(function (array $row) {
                $values = array_values($row);
                if (count($values) >= 2 && $values[0] && $values[1]) {
                    return [
                        'name' => trim((string) $values[0]),
                        'date_of_birth' => Carbon::parse($values[1])->toDateString(),
                    ];
                }

                return null;
            })
            ->filter()
            ->collect();
    }

    protected function storeUsers(): void
    {
        $this->users->each(function ($userData) {
            User::firstOrCreate(
                ['name' => $userData['name'], 'date_of_birth' => $userData['date_of_birth']],
                $userData
            );
        });
    }

    protected function calculateCakeDays(): Collection
    {
        $this->generateInitialCakeDays();

        return $this->applyCakeRules();
    }

    protected function generateInitialCakeDays(): void
    {
        foreach (User::all() as $user) {
            $dateOfBirth = Carbon::parse($user->date_of_birth);
            $birthday = Carbon::create($this->year, $dateOfBirth->month, $dateOfBirth->day);
            $cakeDay = $this->getNextWorkingDay($birthday);

            $this->cakeDays->push([
                'name' => $user->name,
                'birthday' => $birthday,
                'cake_day' => $cakeDay,
            ]);
        }
    }

    protected function getNextWorkingDay(Carbon $date): Carbon
    {
        $nextDay = $date->copy()->addDay();

        // If birthday is on weekend/holiday, they get the next working day off too
        if ($this->isNonWorkingDay($date)) {
            $nextDay->addDay(); // Skip the next working day as well
        }

        while ($this->isNonWorkingDay($nextDay)) {
            $nextDay->addDay();
        }

        return $nextDay;
    }

    protected function isNonWorkingDay(Carbon $date): bool
    {
        if ($date->isWeekend()) {
            return true;
        }

        foreach ($this->holidays as $holiday => $monthDay) {
            if ($date->format('m-d') === $monthDay) {
                return true;
            }
        }

        return false;
    }

    protected function applyCakeRules(): Collection
    {
        $groupedByDate = $this->cakeDays->groupBy(fn ($item) => $item['cake_day']->toDateString());
        $sortedDates = $groupedByDate->keys()->sort();
        $processedDates = collect();

        foreach ($sortedDates as $date) {
            if ($processedDates->contains($date)) {
                continue; // Already processed as part of consecutive days
            }

            $dayItems = $groupedByDate->get($date);
            $cakeDate = Carbon::parse($date);
            $names = $dayItems->pluck('name')->toArray();

            // Check if next day also has cakes (consecutive rule)
            $nextDay = $cakeDate->copy()->addDay()->toDateString();
            if ($groupedByDate->has([$nextDay]) && ! $processedDates->contains($nextDay)) {
                // Consolidate to the second day with large cake
                $nextDayItems = $groupedByDate->get($nextDay);
                $cakeDate = $cakeDate->addDay(); // Move to second day
                $names = array_merge($names, $nextDayItems->pluck('name')->toArray());
                $processedDates->push($date); // Mark first day as processed
                $processedDates->push($nextDay); // Mark second day as processed

                $this->finalCakeDays->push([
                    'date' => $cakeDate,
                    'cake_type' => CakeType::Large,
                    'names' => $names,
                ]);
            } else {
                // Regular cake day
                if ($this->cakeFreeDays->contains($date)) {
                    $cakeDate = $this->postponeCakeDay($cakeDate);
                }

                $cakeType = count($names) > 1 ? CakeType::Large : CakeType::Small;

                $this->finalCakeDays->push([
                    'date' => $cakeDate,
                    'cake_type' => $cakeType,
                    'names' => $names,
                ]);

                $processedDates->push($date);
            }

            $this->cakeFreeDays->push($cakeDate->copy()->addDay()->toDateString());
        }

        return $this->finalCakeDays->sortBy('date');
    }

    protected function postponeCakeDay(Carbon $cakeDate): Carbon
    {
        do {
            $cakeDate = $this->getNextWorkingDay($cakeDate);
        } while ($this->cakeFreeDays->contains($cakeDate->toDateString()));

        return $cakeDate;
    }

    protected function hasCakeOnConsecutiveDays(Carbon $proposedDate): bool
    {
        $previousDay = $proposedDate->copy()->subDay();

        return $this->finalCakeDays->contains(function ($cakeDay) use ($previousDay) {
            return $cakeDay['date']->isSameDay($previousDay);
        });
    }

    protected function storeCakeDays(): void
    {
        CakeDay::truncate();

        $this->finalCakeDays->each(function ($cakeDay) {
            CakeDay::create([
                'date' => $cakeDay['date'],
                'cake_type' => $cakeDay['cake_type'],
                'names' => $cakeDay['names'],
            ]);
        });
    }

    public function getCakeDaysForYear(): Collection
    {
        return CakeDay::whereYear('date', $this->year)
            ->orderBy('date')
            ->get();
    }
}
