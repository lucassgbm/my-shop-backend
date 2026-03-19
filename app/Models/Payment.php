<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Payment extends Model {
    protected $fillable = ['order_id','provider','provider_id','method','status','amount','payload'];
    protected $casts = ['amount' => 'decimal:2','payload' => 'array'];
    public function order(): BelongsTo { return $this->belongsTo(Order::class); }
}
