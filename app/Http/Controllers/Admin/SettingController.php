<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Support\ActivityLogger;
use App\Support\SiteSettings;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class SettingController extends Controller
{
    public function __construct(private SiteSettings $siteSettings) {}

    public function edit(): View
    {
        return view('admin.settings.edit', [
            'fieldGroups' => collect(SiteSettings::FIELDS)->groupBy('group', preserveKeys: true),
            'values' => $this->siteSettings->current(),
        ]);
    }

    public function update(Request $request, ActivityLogger $activityLogger): RedirectResponse
    {
        $validated = $request->validate(
            collect(SiteSettings::FIELDS)->map(fn (array $field): array => $field['rules'])->all(),
        );

        $before = $this->siteSettings->current();
        $this->siteSettings->save($validated);

        $changedKeys = collect($validated)
            ->filter(fn (?string $value, string $key): bool => ($before[$key] ?? null) !== $value)
            ->keys()
            ->all();

        $activityLogger->log('settings.updated', 'Cập nhật cấu hình website', properties: ['changed' => $changedKeys]);

        return back()->with('status', 'Đã lưu cấu hình website.');
    }
}
