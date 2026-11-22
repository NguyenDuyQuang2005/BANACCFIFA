<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReplacesUploadedFile;
use App\Http\Controllers\Controller;
use App\Models\CoachSkill;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

/**
 * "Ranking kỹ năng HLV": one inline-editable list, ranked by usage %.
 */
class CoachSkillController extends Controller
{
    use ReplacesUploadedFile;

    public function index(): View
    {
        return view('admin.tools.coach-skills', [
            'coachSkills' => CoachSkill::query()->orderByDesc('usage_percent')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $coachSkill = new CoachSkill($this->validated($request));
        $coachSkill->icon_path = $this->replaceUpload($request, 'icon', null, 'coach-skills') ?: null;
        $coachSkill->save();

        return back()->with('status', 'Đã thêm kỹ năng HLV.');
    }

    public function update(Request $request, CoachSkill $coachSkill): RedirectResponse
    {
        $coachSkill->fill($this->validated($request));

        $icon = $this->replaceUpload($request, 'icon', $coachSkill->icon_path, 'coach-skills');
        if ($icon !== false) {
            $coachSkill->icon_path = $icon;
        }

        $coachSkill->save();

        return back()->with('status', 'Đã cập nhật '.$coachSkill->name.'.');
    }

    public function destroy(CoachSkill $coachSkill): RedirectResponse
    {
        if ($coachSkill->icon_path !== null) {
            Storage::disk('public')->delete($coachSkill->icon_path);
        }

        $coachSkill->delete();

        return back()->with('status', 'Đã xóa kỹ năng HLV.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:150'],
            'usage_percent' => ['required', 'numeric', 'min:0', 'max:100'],
            'icon' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif'],
            'remove_icon' => ['sometimes', 'boolean'],
            'is_visible' => ['required', 'boolean'],
        ]);

        unset($validated['icon'], $validated['remove_icon']);

        return $validated;
    }
}
