<?php

namespace App\Http\Controllers;

use App\Models\ActivityLog;
use Illuminate\Http\Request;

class ActivityLogController extends Controller
{
    public function index()
    {
        return ActivityLog::with('user')
            ->latest()
            ->paginate(20);
    }
}
