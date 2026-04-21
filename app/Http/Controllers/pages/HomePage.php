<?php

namespace App\Http\Controllers\pages;

use App\Http\Controllers\Controller;
use App\Services\Dashboard\DashboardCountsService;

class HomePage extends Controller
{
    public function __construct(private readonly DashboardCountsService $dashboardCountsService)
    {
    }

    public function index()
    {
        return view('content.pages.pages-home', $this->dashboardCountsService->build(auth()->user()));
    }
}
