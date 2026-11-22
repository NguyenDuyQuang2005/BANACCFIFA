<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Database\Factories\PlayerFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

/**
 * A player card shown in the "Dữ liệu cầu thủ" and "Giờ reset" tools. Data is entered by admins.
 */
#[Fillable([
    'player_season_id',
    'name',
    'position',
    'ovr',
    'pace',
    'stamina',
    'stars',
    'image_path',
    'reset_odd_minute',
    'reset_even_minute',
    'is_visible',
])]
class Player extends Model
{
    /** @use HasFactory<PlayerFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'ovr' => 'integer',
            'pace' => 'integer',
            'stamina' => 'integer',
            'stars' => 'integer',
            'reset_odd_minute' => 'integer',
            'reset_even_minute' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<PlayerSeason, $this>
     */
    public function season(): BelongsTo
    {
        return $this->belongsTo(PlayerSeason::class, 'player_season_id');
    }

    #[Scope]
    protected function visible(Builder $query): Builder
    {
        return $query->where('is_visible', true);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path === null ? null : Storage::disk('public')->url($this->image_path);
    }

    public function hasResetSchedule(): bool
    {
        return $this->reset_odd_minute !== null || $this->reset_even_minute !== null;
    }

    /**
     * e.g. "Lẻ 55 - Chẵn 25": the price resets at minute 55 of odd hours and minute 25 of even hours.
     */
    public function resetLabel(): ?string
    {
        $parts = array_filter([
            $this->reset_odd_minute !== null ? 'Lẻ '.str_pad((string) $this->reset_odd_minute, 2, '0', STR_PAD_LEFT) : null,
            $this->reset_even_minute !== null ? 'Chẵn '.str_pad((string) $this->reset_even_minute, 2, '0', STR_PAD_LEFT) : null,
        ]);

        return $parts === [] ? null : implode(' - ', $parts);
    }

    /**
     * Next moment the price resets, counting from $now (Vietnam time).
     */
    public function nextResetAt(?CarbonImmutable $now = null): ?CarbonImmutable
    {
        if (! $this->hasResetSchedule()) {
            return null;
        }

        $now ??= CarbonImmutable::now('Asia/Ho_Chi_Minh');
        $hourStart = $now->startOfHour();

        for ($offset = 0; $offset <= 2; $offset++) {
            $hour = $hourStart->addHours($offset);
            $minute = $hour->hour % 2 === 1 ? $this->reset_odd_minute : $this->reset_even_minute;

            if ($minute === null) {
                continue;
            }

            $candidate = $hour->addMinutes($minute);

            if ($candidate->greaterThan($now)) {
                return $candidate;
            }
        }

        return null;
    }
}
