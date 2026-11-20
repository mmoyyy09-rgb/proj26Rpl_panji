<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $activities = ActivityLog::with('user')
            ->when(
                $request->user()->role !== 'admin',
                fn ($query) => $query->where('user_id', $request->user()->id)
            )
            ->latest()
            ->paginate(20);

        return view('activity-logs.index', compact('activities'));
    }
}
