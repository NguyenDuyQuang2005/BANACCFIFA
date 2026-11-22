<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Admin\Concerns\ReplacesUploadedFile;
use App\Http\Controllers\Controller;
use App\Models\SkillMove;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class SkillMoveController extends Controller
{
    use ReplacesUploadedFile;

    public function index(): View
    {
        return view('admin.tools.skills.index', [
            'skills' => SkillMove::query()->orderBy('level')->orderBy('sort_order')->orderBy('id')->paginate(30),
        ]);
    }

    public function create(): View
    {
        return view('admin.tools.skills.form', ['skill' => null]);
    }

    public function store(Request $request): RedirectResponse
    {
        $skill = new SkillMove($this->validated($request));
        $skill->media_path = $this->replaceUpload($request, 'media', null, 'skills') ?: null;
        $skill->save();

        return redirect()->route('admin.tool-skills.index')->with('status', 'Đã thêm kỹ thuật.');
    }

    public function edit(SkillMove $skill): View
    {
        return view('admin.tools.skills.form', ['skill' => $skill]);
    }

    public function update(Request $request, SkillMove $skill): RedirectResponse
    {
        $skill->fill($this->validated($request));

        $media = $this->replaceUpload($request, 'media', $skill->media_path, 'skills');
        if ($media !== false) {
            $skill->media_path = $media;
        }

        $skill->save();

        return redirect()->route('admin.tool-skills.index')->with('status', 'Đã cập nhật kỹ thuật.');
    }

    public function destroy(SkillMove $skill): RedirectResponse
    {
        if ($skill->media_path !== null) {
            Storage::disk('public')->delete($skill->media_path);
        }

        $skill->delete();

        return back()->with('status', 'Đã xóa kỹ thuật.');
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'level' => ['required', 'integer', 'min:1', 'max:10'],
            'description' => ['nullable', 'string', 'max:255'],
            'keyboard_keys' => ['nullable', 'string', 'max:120'],
            'controller_keys' => ['nullable', 'string', 'max:120'],
            'media' => ['nullable', 'file', 'max:20480', 'mimetypes:image/gif,image/jpeg,image/png,image/webp,video/mp4,video/webm'],
            'remove_media' => ['sometimes', 'boolean'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:9999'],
            'is_visible' => ['required', 'boolean'],
        ], [
            'media.mimetypes' => 'Chỉ nhận ảnh/GIF (gif, jpg, png, webp) hoặc video (mp4, webm).',
        ]);

        unset($validated['media'], $validated['remove_media']);

        return $validated;
    }
}
