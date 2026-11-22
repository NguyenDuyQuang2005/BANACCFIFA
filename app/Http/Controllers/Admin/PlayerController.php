<?php

namespace App\Http\Controllers\Admin;

use App\Actions\ImportPlayersFromCsvAction;
use App\Http\Controllers\Admin\Concerns\ReplacesUploadedFile;
use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerSeason;
use App\Support\ActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PlayerController extends Controller
{
    use ReplacesUploadedFile;

    public function index(Request $request): View
    {
        $search = trim((string) $request->query('q', ''));

        return view('admin.tools.players.index', [
            'search' => $search,
            'seasons' => PlayerSeason::query()->withCount('players')->orderBy('sort_order')->orderBy('name')->get(),
            'players' => Player::query()
                ->with('season')
                ->when($search !== '', fn ($query) => $query->where('name', 'like', '%'.$search.'%'))
                ->orderByDesc('ovr')
                ->orderBy('name')
                ->paginate(30)
                ->withQueryString(),
        ]);
    }

    public function create(): View
    {
        return view('admin.tools.players.form', [
            'player' => null,
            'seasons' => PlayerSeason::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $player = new Player($this->validated($request));
        $player->image_path = $this->replaceUpload($request, 'image', null, 'players') ?: null;
        $player->save();

        return redirect()->route('admin.tool-players.index')->with('status', 'Đã thêm cầu thủ '.$player->name.'.');
    }

    public function edit(Player $player): View
    {
        return view('admin.tools.players.form', [
            'player' => $player,
            'seasons' => PlayerSeason::query()->orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function update(Request $request, Player $player): RedirectResponse
    {
        $player->fill($this->validated($request));

        $image = $this->replaceUpload($request, 'image', $player->image_path, 'players');
        if ($image !== false) {
            $player->image_path = $image;
        }

        $player->save();

        return redirect()->route('admin.tool-players.index')->with('status', 'Đã cập nhật '.$player->name.'.');
    }

    public function destroy(Player $player): RedirectResponse
    {
        if ($player->image_path !== null) {
            Storage::disk('public')->delete($player->image_path);
        }

        $player->delete();

        return back()->with('status', 'Đã xóa cầu thủ.');
    }

    public function import(Request $request, ImportPlayersFromCsvAction $importPlayers, ActivityLogger $activityLogger): RedirectResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'max:20480', 'mimes:csv,txt'],
        ]);

        $result = $importPlayers->handle($request->file('file')->getRealPath());

        $activityLogger->log('players.imported', 'Nhập cầu thủ từ CSV: thêm '.$result['created'].', cập nhật '.$result['updated'], properties: [
            'errors' => count($result['errors']),
        ]);

        return back()
            ->with('status', "Nhập xong: thêm {$result['created']}, cập nhật {$result['updated']}, lỗi ".count($result['errors']).'.')
            ->with('importErrors', array_slice($result['errors'], 0, 20));
    }

    public function template(): StreamedResponse
    {
        return response()->streamDownload(function (): void {
            echo "\u{FEFF}".implode(',', ImportPlayersFromCsvAction::COLUMNS)."\n";
            echo "Tên cầu thủ,ICON,ST,120,125,118,5,55,25\n";
        }, 'mau-nhap-cau-thu.csv', ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    /**
     * @return array<string, mixed>
     */
    private function validated(Request $request): array
    {
        $request->merge(['position' => mb_strtoupper((string) $request->input('position'))]);

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:120'],
            'player_season_id' => ['nullable', 'exists:player_seasons,id'],
            'position' => ['required', Rule::in(config('tools.player_positions', []))],
            'ovr' => ['required', 'integer', 'min:1', 'max:300'],
            'pace' => ['nullable', 'integer', 'min:1', 'max:300'],
            'stamina' => ['nullable', 'integer', 'min:1', 'max:300'],
            'stars' => ['nullable', 'integer', 'min:1', 'max:5'],
            'reset_odd_minute' => ['nullable', 'integer', 'min:0', 'max:59'],
            'reset_even_minute' => ['nullable', 'integer', 'min:0', 'max:59'],
            'image' => ['nullable', 'image', 'max:20480', 'mimes:jpg,jpeg,png,webp,gif'],
            'remove_image' => ['sometimes', 'boolean'],
            'is_visible' => ['required', 'boolean'],
        ]);

        unset($validated['image'], $validated['remove_image']);

        return $validated;
    }
}
