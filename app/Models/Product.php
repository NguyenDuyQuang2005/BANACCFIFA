<?php

namespace App\Models;

use App\Enums\ProductStatus;
use Database\Factories\ProductFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Attributes\Scope;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Number;

#[Fillable([
    'category_id',
    'code',
    'slug',
    'name',
    'description',
    'price',
    'original_price',
    'bp',
    'squad',
    'ovr',
    'featured_players',
    'status',
    'is_featured',
    'sold_at',
    'credentials',
])]
#[Hidden(['credentials'])]
class Product extends Model
{
    /** @use HasFactory<ProductFactory> */
    use HasFactory, SoftDeletes;

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'original_price' => 'decimal:2',
            'bp' => 'decimal:2',
            'ovr' => 'integer',
            'featured_players' => 'array',
            'status' => ProductStatus::class,
            'is_featured' => 'boolean',
            'sold_at' => 'datetime',
            'credentials' => 'encrypted',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * @return BelongsTo<Category, $this>
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    /**
     * @return HasMany<ProductImage, $this>
     */
    public function images(): HasMany
    {
        return $this->hasMany(ProductImage::class)->orderBy('sort_order')->orderBy('id');
    }

    /**
     * @return HasMany<OrderItem, $this>
     */
    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    /**
     * @return HasOne<ProductImage, $this>
     */
    public function coverImage(): HasOne
    {
        return $this->hasOne(ProductImage::class)->where('is_primary', true);
    }

    #[Scope]
    protected function public(Builder $query): Builder
    {
        return $query->whereIn('status', [ProductStatus::Available, ProductStatus::Sold]);
    }

    #[Scope]
    protected function available(Builder $query): Builder
    {
        return $query->where('status', ProductStatus::Available);
    }

    #[Scope]
    protected function featured(Builder $query): Builder
    {
        return $query->available()->where('is_featured', true);
    }

    #[Scope]
    protected function search(Builder $query, ?string $term): Builder
    {
        if ($term === null || $term === '') {
            return $query;
        }

        $like = '%'.addcslashes($term, '%_\\').'%';

        return $query->where(function (Builder $builder) use ($like, $term): void {
            $builder->where('code', $term)
                ->orWhere('code', 'like', $like)
                ->orWhere('name', 'like', $like);
        });
    }

    public function isAvailable(): bool
    {
        return $this->status === ProductStatus::Available;
    }

    public function coverUrl(): ?string
    {
        $cover = $this->coverImage ?? $this->images->first();

        return $cover?->url();
    }

    public function formattedPrice(): string
    {
        return Number::format((float) $this->price, locale: 'vi').'₫';
    }

    public function formattedOriginalPrice(): ?string
    {
        if ($this->original_price === null) {
            return null;
        }

        return Number::format((float) $this->original_price, locale: 'vi').'₫';
    }

    public function formattedBp(): string
    {
        if ($this->bp === null) {
            return '—';
        }

        return Number::format((float) $this->bp, locale: 'vi');
    }
}
