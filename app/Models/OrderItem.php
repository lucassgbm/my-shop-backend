<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class OrderItem extends Model {
    protected $fillable = ['order_id','product_id','variant_id','quantity','unit_price','total_price','product_snapshot'];
    protected $casts = ['unit_price' => 'decimal:2','total_price' => 'decimal:2','product_snapshot' => 'array'];
    public function order(): BelongsTo   { return $this->belongsTo(Order::class); }
    public function product(): BelongsTo { return $this->belongsTo(Product::class); }
    public function variant(): BelongsTo { return $this->belongsTo(ProductVariant::class); }
}
