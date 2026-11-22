<?php

namespace App\Http\Controllers;

use App\Models\CoachSkill;
use App\Models\Player;
use App\Models\PlayerSeason;
use App\Models\SkillMove;
use App\Support\Tools\TaxCalculator;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class ToolController extends Controller
{
    public function index(): View
    {
        return view('tools.index', [
            'tools' => collect(config('tools.modules', [])),
        ]);
    }

    public function show(Request $request, string $tool): View|RedirectResponse
    {
        $module = collect(config('tools.modules', []))->firstWhere('slug', $tool);

        abort_if($module === null, 404);

        if (($module['requires_auth'] ?? false) && $request->user() === null) {
            return redirect()->guest(route('login'));
        }

        return match ($tool) {
            'tinh-thue' => $this->tax($module),
            'dao-tao-ovr' => $this->ovr($module),
            'ky-thuat' => $this->skills($module),
            'ranking' => $this->coachRanking($module),
            'du-lieu-cau-thu' => $this->players($request, $module),
            'gio-reset' => $this->resetHours($request, $module),
            default => abort(404),
        };
    }

    public function player(Player $player): View
    {
        abort_unless($player->is_visible, 404);

        return view('tools.player', [
            'player' => $player->load('season'),
            'nextReset' => $player->nextResetAt(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function tax(array $module): View
    {
        return view('tools.tax', [
            'tool' => $module,
            'tax' => config('tools.tax'),
            'example' => app(TaxCalculator::class)->calculate(1_000_000, 0, 0),
        ]);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function ovr(array $module): View
    {
        $ovr = config('tools.ovr');

        return view('tools.ovr', [
            'tool' => $module,
            'ovr' => $ovr,
            'clientConfig' => [
                'positions' => collect($ovr['positions'])->map(fn (array $position): array => [
                    'label' => $position['label'],
                    'weights' => $position['weights'],
                ]),
                'statNames' => $ovr['stat_names'],
                'maxTrained' => $ovr['max_trained_stats'],
                'maxPerStat' => $ovr['max_per_stat'],
            ],
        ]);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function skills(array $module): View
    {
        $skills = SkillMove::query()->visible()->get();

        return view('tools.skills', [
            'tool' => $module,
            'skills' => $skills,
            'levels' => $skills->pluck('level')->unique()->sort()->values(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function coachRanking(array $module): View
    {
        return view('tools.coach-ranking', [
            'tool' => $module,
            'coachSkills' => CoachSkill::query()->ranked()->get(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function players(Request $request, array $module): View
    {
        $filters = $this->playerFilters($request);
        $compareIds = collect((array) $request->query('compare', []))->map(fn ($id): int => (int) $id)->filter()->unique()->take(2);

        return view('tools.players', [
            'tool' => $module,
            'filters' => $filters,
            'seasons' => PlayerSeason::query()->orderBy('sort_order')->orderBy('name')->get(),
            'positions' => config('tools.player_positions', []),
            'players' => $this->playerQuery($filters),
            'compareIds' => $compareIds->all(),
            'compared' => $compareIds->isEmpty()
                ? collect()
                : Player::query()->visible()->with('season')->whereKey($compareIds)->get(),
        ]);
    }

    /**
     * @param  array<string, mixed>  $module
     */
    private function resetHours(Request $request, array $module): View
    {
        $filters = $this->playerFilters($request);

        return view('tools.reset-hours', [
            'tool' => $module,
            'filters' => $filters,
            'seasons' => PlayerSeason::query()->orderBy('sort_order')->orderBy('name')->get(),
            'players' => $this->playerQuery($filters),
        ]);
    }

    /**
     * @return array{q: string, season: int|null, position: string|null}
     */
    private function playerFilters(Request $request): array
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:80'],
            'season' => ['nullable', 'integer'],
            'position' => ['nullable', Rule::in(config('tools.player_positions', []))],
        ]);

        return [
            'q' => trim((string) ($validated['q'] ?? '')),
            'season' => isset($validated['season']) ? (int) $validated['season'] : null,
            'position' => $validated['position'] ?? null,
        ];
    }

    /**
     * @param  array{q: string, season: int|null, position: string|null}  $filters
     * @return LengthAwarePaginator<int, Player>
     */
    private function playerQuery(array $filters): LengthAwarePaginator
    {
        return Player::query()
            ->visible()
            ->with('season')
            ->when($filters['q'] !== '', fn ($query) => $query->where('name', 'like', '%'.$filters['q'].'%'))
            ->when($filters['season'] !== null, fn ($query) => $query->where('player_season_id', $filters['season']))
            ->when($filters['position'] !== null, fn ($query) => $query->where('position', $filters['position']))
            ->orderByDesc('ovr')
            ->orderBy('name')
            ->paginate(30)
            ->withQueryString();
    }
}
