<?php

namespace App\Models;

use Database\Factories\ServiceFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

/**
 * An FC Online service (e.g. Bơm BP) sold by contacting the admin, not through the wallet checkout.
 */
#[Fillable([
    'code',
    'name',
    'description',
    'image_path',
    'contact_label',
    'contact_url',
    'sort_order',
    'is_visible',
])]
class Service extends Model
{
    /** @use HasFactory<ServiceFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'is_visible' => 'boolean',
            'sort_order' => 'integer',
        ];
    }

    #[Scope]
    protected function visible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        return $this->image_path === null ? null : Storage::disk('public')->url($this->image_path);
    }

    /**
     * The service's own contact link, falling back to the site-wide Zalo link.
     */
    public function contactUrl(): ?string
    {
        return $this->contact_url ?: config('site.social.zalo');
    }
}
