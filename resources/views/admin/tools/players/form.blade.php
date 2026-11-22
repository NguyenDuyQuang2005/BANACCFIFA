<x-layouts.admin :title="$player ? 'Sửa cầu thủ' : 'Thêm cầu thủ'">
    <a href="{{ route('admin.tool-players.index') }}" class="text-sm text-muted hover:text-accent">← Cầu thủ</a>
    <h1 class="mt-3 font-display text-3xl font-semibold">{{ $player ? 'Sửa cầu thủ' : 'Thêm cầu thủ' }}</h1>
    <form method="post" action="{{ $player ? route('admin.tool-players.update', $player) : route('admin.tool-players.store') }}" enctype="multipart/form-data" class="mt-8 grid max-w-2xl gap-4">
        @csrf
        @if ($player) @method('put') @endif
        <x-form-input name="name" label="Tên cầu thủ" :value="old('name', $player?->name)" required maxlength="120" />
        @error('name') <p class="text-sm text-primary">{{ $message }}</p> @enderror
        <div class="grid gap-4 sm:grid-cols-2">
            <label class="grid gap-2 text-sm">
                <span class="font-medium">Mùa thẻ</span>
                <select name="player_season_id" class="rounded-xl border border-line bg-canvas px-3 py-2.5">
                    <option value="">— Không có —</option>
                    @foreach ($seasons as $season)
                        <option value="{{ $season->id }}" @selected((string) old('player_season_id', $player?->player_season_id) === (string) $season->id)>{{ $season->code }} · {{ $season->name }}</option>
                    @endforeach
                </select>
            </label>
            <label class="grid gap-2 text-sm">
                <span class="font-medium">Vị trí</span>
                <select name="position" class="rounded-xl border border-line bg-canvas px-3 py-2.5" required>
                    @foreach (config('tools.player_positions') as $position)
                        <option value="{{ $position }}" @selected(old('position', $player?->position) === $position)>{{ $position }}</option>
                    @endforeach
                </select>
            </label>
        </div>
        <div class="grid gap-4 sm:grid-cols-4">
            <x-form-input name="ovr" type="number" label="OVR" :value="old('ovr', $player?->ovr)" min="1" max="300" required />
            <x-form-input name="pace" type="number" label="Tốc độ" :value="old('pace', $player?->pace)" min="1" max="300" />
            <x-form-input name="stamina" type="number" label="Thể lực" :value="old('stamina', $player?->stamina)" min="1" max="300" />
            <x-form-input name="stars" type="number" label="Sao (1-5)" :value="old('stars', $player?->stars)" min="1" max="5" />
        </div>
        @foreach (['ovr', 'pace', 'stamina', 'stars', 'position'] as $field)
            @error($field) <p class="text-sm text-primary">{{ $message }}</p> @enderror
        @endforeach
        <fieldset class="grid gap-3 rounded-2xl border border-line p-4 sm:grid-cols-2">
            <legend class="px-1 text-sm font-medium">Giờ reset giá (phút trong giờ, để trống nếu không có)</legend>
            <x-form-input name="reset_odd_minute" type="number" label="Giờ lẻ — phút" :value="old('reset_odd_minute', $player?->reset_odd_minute)" min="0" max="59" placeholder="VD: 55" />
            <x-form-input name="reset_even_minute" type="number" label="Giờ chẵn — phút" :value="old('reset_even_minute', $player?->reset_even_minute)" min="0" max="59" placeholder="VD: 25" />
        </fieldset>
        <div class="grid gap-2 rounded-2xl border border-line p-4">
            <label class="grid gap-2 text-sm"><span class="font-medium">Ảnh cầu thủ (jpg, png, webp, gif · tối đa 20MB)</span><input type="file" name="image" accept="image/jpeg,image/png,image/webp,image/gif" class="text-sm"></label>
            @if ($player?->imageUrl())
                <img src="{{ $player->imageUrl() }}" alt="" class="h-24 w-24 rounded-2xl object-cover">
                <label class="flex items-center gap-2 text-sm text-muted"><input type="checkbox" name="remove_image" value="1"> Gỡ ảnh hiện tại</label>
            @endif
            @error('image') <p class="text-sm text-primary">{{ $message }}</p> @enderror
        </div>
        <input type="hidden" name="is_visible" value="0">
        <label class="flex items-center gap-2 text-sm"><input type="checkbox" name="is_visible" value="1" @checked(old('is_visible', $player?->is_visible ?? true))> Hiện trên web</label>
        <button class="justify-self-start rounded-full bg-primary px-5 py-3 text-sm font-semibold text-white">Lưu</button>
    </form>
</x-layouts.admin>
