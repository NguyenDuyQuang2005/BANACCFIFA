<?php

namespace App\Models;

use Database\Factories\SkillMoveFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable(['name', 'level', 'description', 'keyboard_keys', 'controller_keys', 'media_path', 'sort_order', 'is_visible'])]
class SkillMove extends Model
{
    /** @use HasFactory<SkillMoveFactory> */
    use HasFactory;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'level' => 'integer',
            'sort_order' => 'integer',
            'is_visible' => 'boolean',
        ];
    }

    #[Scope]
    protected function visible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('level')->orderBy('sort_order')->orderBy('id');
    }

    public function mediaUrl(): ?string
    {
        return $this->media_path === null ? null : Storage::disk('public')->url($this->media_path);
    }

    public function isVideo(): bool
    {
        return $this->media_path !== null && preg_match('/\.(mp4|webm)$/i', $this->media_path) === 1;
    }
}
