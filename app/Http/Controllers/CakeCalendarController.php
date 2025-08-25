<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CakeDayService;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class CakeCalendarController extends Controller
{
    public function __construct(
        protected CakeDayService $cakeDayService
    ) {}

    public function index(Request $request): Response
    {
        $year = $request->integer('year', now()->year);

        $cakeService = new CakeDayService($year);
        $cakeDays = $cakeService->getCakeDaysForYear();

        return Inertia::render('Home', [
            'cakeDays' => $cakeDays->map(function ($cakeDay) {
                return [
                    'id' => $cakeDay->id,
                    'date' => $cakeDay->date->format('Y-m-d'),
                    'cakeType' => $cakeDay->cake_type->value,
                    'names' => $cakeDay->names,
                ];
            }),
            'currentYear' => $year,
        ]);
    }
}
