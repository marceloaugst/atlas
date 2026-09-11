<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\UpdateProductStatistics;
use Illuminate\Support\Facades\Cache;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $stats = Cache::get(UpdateProductStatistics::CACHE_KEY);

        if (! $stats) {
            // Cold cache (fresh install, or the scheduled job hasn't run yet):
            // compute it inline this one time so the dashboard isn't empty,
            // instead of waiting for the next scheduled run.
            UpdateProductStatistics::dispatchSync();
            $stats = Cache::get(UpdateProductStatistics::CACHE_KEY);
        }

        return Inertia::render('Admin/Dashboard', [
            'metrics' => $stats['metrics'],
            'topProducts' => $stats['topProducts'],
            'updatedAt' => $stats['updatedAt'],
        ]);
    }
}
