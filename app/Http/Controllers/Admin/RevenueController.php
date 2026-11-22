<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\RevenueReport;
use Illuminate\Http\Request;
use Illuminate\View\View;

class RevenueController extends Controller
{
    public function __invoke(Request $request): View
    {
        $report = RevenueReport::fromRequest($request->query('ky'));

        return view('admin.revenue', [
            'report' => $report,
            'periods' => RevenueReport::PERIODS,
            'summary' => $report->summary(),
            'series' => $report->series(),
            'categories' => $report->byCategory(),
        ]);
    }
}
