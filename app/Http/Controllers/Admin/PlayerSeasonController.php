<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReplacesUploadedFile;
use App\Http\Controllers\Controller;
use App\Models\PlayerSeason;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

/**
 * Card seasons (mùa thẻ) are managed inline on the admin players page.
 */
class PlayerSeasonController extends Controller
{
    use ReplacesUploadedFile;

    public function store(Request $request): RedirectResponse
    {
        $season = new PlayerSeason($this->validated($request));
        $season->icon_path = $this->replaceUpload($request, 'icon', null, 'seasons') ?: null;
        $season->save();

        return back()->with('status', 'Đã thêm mùa thẻ '.$season->code.'.');
    }

    public function update(Request $request, PlayerSeason $season): RedirectResponse
    {
        $season->fill($this->validated($request, $season));

        $icon = $this->replaceUpload($request, 'icon', $season->icon_path, 'seasons');
        if ($icon !== false) {
            $season->icon_path = $icon;
        }

        $season->save();

        return back()->with('status', 'Đã cập nhật mùa thẻ '.$season->code.'.');
    }

    public function destroy(PlayerSeason $season): RedirectResponse
    {
        if ($season->icon_path !== null) {
            Storage::disk('public')->delete($season->icon_path);
        }

        $season->delete();

        return back()->with('status', 'Đã xóa mùa thẻ. Cầu thủ thuộc mùa này được giữ lại, không còn gắn mùa.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?PlayerSeason $season = null): array
    {
        $request->merge(['code' => mb_strtoupper(trim((string) $request->input('code')))]);

        $validated = $request->validate([
            'code' => ['required', 'string', 'max:20', 'regex:/^[A-Z0-9_\-]+$/', Rule::unique('player_seasons', 'code')->ignore($season)],
            'name' => ['required', 'string', 'max:100'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'icon' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif'],
            'remove_icon' => ['sometimes', 'boolean'],
        ]);

        unset($validated['icon'], $validated['remove_icon']);

        return $validated;
    }
}
