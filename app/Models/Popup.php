<?php

namespace App\Models;

use Database\Factories\PopupFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'title',
    'content',
    'media_type',
    'media_path',
    'button_label',
    'button_url',
    'dismiss_minutes',
    'sort_order',
    'is_active',
    'starts_at',
    'ends_at',
])]
class Popup extends Model
{
    /** @use HasFactory<PopupFactory> */
    use HasFactory;

    public const MEDIA_NONE = 'none';

    public const MEDIA_IMAGE = 'image';

    public const MEDIA_VIDEO = 'video';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'dismiss_minutes' => 'integer',
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
        ];
    }

    /**
     * Popups that are switched on and inside their display window.
     */
    #[Scope]
    protected function live(Builder $query): Builder
    {
        return $query->where('is_active', true)
            ->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()))
            ->orderBy('sort_order')
            ->orderByDesc('id');
    }

    public function mediaUrl(): ?string
    {
        return $this->media_path === null ? null : Storage::disk('public')->url($this->media_path);
    }

    /**
     * Changes whenever the popup is edited, so an edited popup shows again even if a visitor dismissed the old one.
     */
    public function dismissKey(): string
    {
        return 'popup-'.$this->id.'-'.$this->updated_at?->timestamp;
    }
}
