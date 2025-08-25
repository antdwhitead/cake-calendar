<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Services\CakeDayService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;
use Inertia\Response;

class FileUploadController extends Controller
{
    public function __construct(
        protected CakeDayService $cakeDayService
    ) {}

    public function show(): Response
    {
        return Inertia::render('Upload');
    }

    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:txt,csv,xlsx', 'max:2048'],
            'year' => ['nullable', 'integer', 'min:2000', 'max:2100'],
        ]);

        try {
            $year = $request->integer('year', now()->year);
            $service = new CakeDayService($year);
            
            $service->processFileUpload($request->file('file'));

            return redirect()->route('home', ['year' => $year])
                ->with('success', 'File uploaded and processed successfully!');

        } catch (\InvalidArgumentException $e) {
            throw ValidationException::withMessages([
                'file' => [$e->getMessage()],
            ]);
        } catch (\Exception $e) {
            throw ValidationException::withMessages([
                'file' => ['An error occurred while processing the file.'],
            ]);
        }
    }
}