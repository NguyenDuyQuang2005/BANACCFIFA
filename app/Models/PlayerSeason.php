<?php

namespace App\Models;

use Database\Factories\PlayerSeasonFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['code', 'name', 'icon_path', 'sort_order'])]
class PlayerSeason extends Model
{
    /** @use HasFactory<PlayerSeasonFactory> */
    use HasFactory;

    /**
     * @return HasMany<Player, $this>
     */
    public function players(): HasMany
    {
        return $this->hasMany(Player::class);
    }

    public function iconUrl(): ?string
    {
        return $this->icon_path === null ? null : Storage::disk('public')->url($this->icon_path);
    }
}
