<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class ProductVariant extends Model {
    protected $fillable = ['product_id','size','color','sku','stock','price'];
    protected $casts = ['price' => 'decimal:2'];
    const SIZES = ['PP','P','M','G','GG','XGG'];
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function isInStock(): bool { return $this->stock > 0; }
}
