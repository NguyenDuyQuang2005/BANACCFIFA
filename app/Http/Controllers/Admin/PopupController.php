<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Popup;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class PopupController extends Controller
{
    public function index(): View
    {
        return view('admin.popups.index', [
            'popups' => Popup::query()->orderBy('sort_order')->orderByDesc('id')->paginate(20),
            'livePopupId' => Popup::query()->live()->value('id'),
        ]);
    }

    public function create(): View
    {
        return view('admin.popups.create');
    }

    public function store(Request $request): RedirectResponse
    {
        Popup::query()->create($this->validated($request));

        return redirect()->route('admin.popups.index')->with('status', 'Đã tạo popup.');
    }

    public function edit(Popup $popup): View
    {
        return view('admin.popups.edit', [
            'popup' => $popup,
        ]);
    }

    public function update(Request $request, Popup $popup): RedirectResponse
    {
        $popup->update($this->validated($request, $popup));

        return redirect()->route('admin.popups.index')->with('status', 'Đã cập nhật popup.');
    }

    public function destroy(Popup $popup): RedirectResponse
    {
        if ($popup->media_path !== null) {
            Storage::disk('public')->delete($popup->media_path);
        }

        $popup->delete();

        return redirect()->route('admin.popups.index')->with('status', 'Đã xóa popup.');
    }

    /**
     * Validates the form and stores/replaces/removes the uploaded image or video.
     *
     * @return array<string, mixed>
     */
    private function validated(Request $request, ?Popup $popup = null): array
    {
        $validated = $request->validate([
            'title' => ['required', 'string', 'max:150'],
            'content' => ['nullable', 'string', 'max:2000'],
            'media_type' => ['required', Rule::in([Popup::MEDIA_NONE, Popup::MEDIA_IMAGE, Popup::MEDIA_VIDEO])],
            'image' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif'],
            'video' => ['nullable', 'file', 'max:20480', 'mimetypes:video/mp4,video/webm'],
            'button_label' => ['nullable', 'string', 'max:60'],
            'button_url' => ['nullable', 'required_with:button_label', 'string', 'max:255', 'regex:/^(https?:\/\/|\/)/'],
            'dismiss_minutes' => ['required', 'integer', 'min:1', 'max:10080'],
            'sort_order' => ['required', 'integer', 'min:0', 'max:999'],
            'is_active' => ['required', 'boolean'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date', 'after_or_equal:starts_at'],
        ], [
            'button_url.regex' => 'Link phải bắt đầu bằng http://, https:// hoặc / (đường dẫn nội bộ).',
            'video.mimetypes' => 'Video phải là file MP4 hoặc WebM.',
        ]);

        $mediaType = $validated['media_type'];
        $upload = match ($mediaType) {
            Popup::MEDIA_IMAGE => $request->file('image'),
            Popup::MEDIA_VIDEO => $request->file('video'),
            default => null,
        };

        $keepsSameMedia = $popup !== null && $popup->media_type === $mediaType && $popup->media_path !== null;

        if ($mediaType !== Popup::MEDIA_NONE && $upload === null && ! $keepsSameMedia) {
            $field = $mediaType === Popup::MEDIA_IMAGE ? 'image' : 'video';
            $request->validate([$field => ['required']], [$field.'.required' => 'Vui lòng tải lên '.($field === 'image' ? 'ảnh' : 'video').'.']);
        }

        if ($upload !== null || $mediaType === Popup::MEDIA_NONE) {
            if ($popup?->media_path !== null) {
                Storage::disk('public')->delete($popup->media_path);
            }

            $validated['media_path'] = $upload?->store('popups', 'public');
        }

        unset($validated['image'], $validated['video']);

        return $validated;
    }
}
