<?php

namespace App\Models;

use Database\Factories\CoachSkillFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'icon_path', 'usage_percent', 'is_visible'])]
class CoachSkill extends Model
{
    /** @use HasFactory<CoachSkillFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'usage_percent' => 'decimal:2',
            'is_visible' => 'boolean',
        ];
    }

    /**
     * Ranked by usage, most used first.
     */
    #[Scope]
    protected function ranked(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderByDesc('usage_percent')->orderBy('id');
    }

    public function iconUrl(): ?string
    {
        return $this->icon_path === null ? null : Storage::disk('public')->url($this->icon_path);
    }
}
