<?php

declare(strict_types=1);

namespace App\Http\Controllers;

use App\Http\Requests\FileUploadRequest;
use App\Services\CakeDayService;
use Illuminate\Http\RedirectResponse;
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

    /**
     * @throws \Exception
     */
    public function store(FileUploadRequest $request): RedirectResponse
    {
        $year = $request->validated('year') ?? now()->year;

        $service = new CakeDayService((int) $year);
        $service->processFileUpload($request->validated('file'));

        return redirect()->route('home', ['year' => $year])
            ->with('success', 'File uploaded and processed successfully!');

    }
}
