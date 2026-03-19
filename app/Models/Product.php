<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Spatie\MediaLibrary\HasMedia;
use Spatie\MediaLibrary\InteractsWithMedia;
use Spatie\MediaLibrary\MediaCollections\Models\Media;

class Product extends Model implements HasMedia
{
    use HasFactory, SoftDeletes, InteractsWithMedia;

    protected $fillable = [
        'category_id', 'name', 'slug', 'description', 'price', 'compare_price',
        'weight', 'width', 'height', 'length', 'is_active', 'is_featured',
        'primary_image_uuid', 'meta_title', 'meta_description',
    ];

    protected $casts = [
        'price'         => 'decimal:2',
        'compare_price' => 'decimal:2',
        'weight'        => 'decimal:3',
        'is_active'     => 'boolean',
        'is_featured'   => 'boolean',
    ];

    public function registerMediaConversions(?Media $media = null): void
    {
        $this->addMediaConversion('thumb')->width(300)->height(300)->nonQueued();
        $this->addMediaConversion('card')->width(600)->height(800)->nonQueued();
    }

    public function registerMediaCollections(): void
    {
        $this->addMediaCollection('images');
    }

    public function category(): BelongsTo { return $this->belongsTo(Category::class); }
    public function variants(): HasMany   { return $this->hasMany(ProductVariant::class); }
    public function reviews(): HasMany    { return $this->hasMany(Review::class); }
    public function wishlists(): HasMany  { return $this->hasMany(Wishlist::class); }

    public function scopeActive($q)   { return $q->where('is_active', true); }
    public function scopeFeatured($q) { return $q->where('is_featured', true); }

    public function getPrimaryMedia()
    {
        $media = $this->getMedia('images');
        if ($media->isEmpty()) return null;
        if ($this->primary_image_uuid) {
            $primary = $media->firstWhere('uuid', $this->primary_image_uuid);
            if ($primary) return $primary;
        }
        return $media->first();
    }

    public function getPrimaryImageUrlAttribute(): string
    {
        $media = $this->getPrimaryMedia();
        if (!$media) return '';
        return $media->hasGeneratedConversion('card') ? $media->getUrl('card') : $media->getUrl();
    }

    public function getPrimaryThumbUrlAttribute(): string
    {
        $media = $this->getPrimaryMedia();
        if (!$media) return '';
        return $media->hasGeneratedConversion('thumb') ? $media->getUrl('thumb') : $media->getUrl();
    }

    public function getAllImagesAttribute(): array
    {
        return $this->getMedia('images')->map(fn($m) => [
            'uuid'  => $m->uuid,
            'url'   => $m->hasGeneratedConversion('card')  ? $m->getUrl('card')  : $m->getUrl(),
            'thumb' => $m->hasGeneratedConversion('thumb') ? $m->getUrl('thumb') : $m->getUrl(),
            'order' => $m->order_column,
        ])->toArray();
    }

    public function getAverageRatingAttribute(): float
    {
        return round($this->reviews()->avg('rating') ?? 0, 1);
    }

    public function getIsOnSaleAttribute(): bool
    {
        return $this->compare_price && $this->compare_price > $this->price;
    }

    public function getDiscountPercentAttribute(): int
    {
        if (!$this->is_on_sale) return 0;
        return (int) round((1 - $this->price / $this->compare_price) * 100);
    }
}
