<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ActivityLog;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ActivityLogController extends Controller
{
    public function index(Request $request): View
    {
        $action = trim((string) $request->query('action', ''));

        return view('admin.activity-logs.index', [
            'action' => $action,
            'actions' => ActivityLog::query()->distinct()->orderBy('action')->pluck('action'),
            'logs' => ActivityLog::query()
                ->with('user')
                ->when($action !== '', fn ($query) => $query->where('action', $action))
                ->latest('id')
                ->paginate(30)
                ->withQueryString(),
        ]);
    }
}
