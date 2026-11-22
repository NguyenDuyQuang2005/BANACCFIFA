<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\View\View;

class ToolController extends Controller
{
    public function index(): View
    {
        return view('tools.index', [
            'tools' => collect(config('tools.modules', [])),
        ]);
    }

    public function show(Request $request, string $tool): View
    {
        $module = collect(config('tools.modules', []))
            ->firstWhere('slug', $tool);

        if ($module === null) {
            abort(404);
        }

        if (($module['requires_auth'] ?? false) && $request->user() === null) {
            return view('pages.placeholder', [
                'title' => $module['name'],
                'heading' => $module['name'],
                'message' => 'Công cụ này sẽ yêu cầu đăng nhập. Hệ thống thành viên được triển khai ở Giai đoạn 3, quyền truy cập sẽ được kiểm soát ở backend.',
            ]);
        }

        return view('tools.show', [
            'tool' => $module,
        ]);
    }
}
