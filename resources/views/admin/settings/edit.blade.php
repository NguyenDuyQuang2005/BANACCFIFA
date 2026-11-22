<x-layouts.admin title="Cấu hình website">
    <h1 class="font-display text-3xl font-semibold">Cấu hình website & SEO</h1>
    <p class="mt-2 text-sm text-muted">Giá trị lưu ở đây sẽ ghi đè biến môi trường (.env). Để trống để dùng giá trị trong .env.</p>

    <form method="post" action="{{ route('admin.settings.update') }}" class="mt-8 grid max-w-3xl gap-8">
        @csrf
        @method('put')
        @foreach ($fieldGroups as $group => $fields)
            <fieldset class="grid gap-4 rounded-3xl border border-line bg-surface p-6">
                <legend class="px-2 font-display text-lg font-semibold text-accent">{{ $group }}</legend>
                @foreach ($fields as $key => $field)
                    @if ($key === 'site_description')
                        <label class="grid gap-2 text-sm">
                            <span class="font-medium">{{ $field['label'] }}</span>
                            <textarea name="{{ $key }}" rows="3" maxlength="300" class="w-full rounded-xl border border-line bg-canvas px-3 py-2.5">{{ old($key, $values[$key]) }}</textarea>
                        </label>
                    @else
                        <x-form-input
                            :name="$key"
                            :label="$field['label']"
                            :type="str_starts_with($key, 'social_') || $key === 'seo_og_image' ? 'url' : ($key === 'contact_email' ? 'email' : 'text')"
                            :value="old($key, $values[$key])"
                        />
                    @endif
                    @error($key) <p class="text-sm text-primary">{{ $message }}</p> @enderror
                @endforeach
            </fieldset>
        @endforeach
        <div>
            <button class="rounded-full bg-primary px-6 py-3 text-sm font-semibold text-white">Lưu cấu hình</button>
        </div>
    </form>
</x-layouts.admin>
