<?php

namespace App\Models;

use Database\Factories\CategoryFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

#[Fillable([
    'name',
    'slug',
    'description',
    'notice',
    'notice_url',
    'icon',
    'image_path',
    'sort_order',
    'is_visible',
])]
class Category extends Model
{
    /** @use HasFactory<CategoryFactory> */
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

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return HasMany<Product, $this>
     */
    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }

    #[Scope]
    protected function visible(Builder $query): Builder
    {
        return $query->where('is_visible', true)->orderBy('sort_order')->orderBy('id');
    }

    public function imageUrl(): ?string
    {
        if ($this->image_path === null) {
            return null;
        }

        return Storage::disk('public')->url($this->image_path);
    }
}
