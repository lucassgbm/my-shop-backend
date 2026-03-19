<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
class Order extends Model {
    const STATUS_PENDING   = 'pending';
    const STATUS_PAID      = 'paid';
    const STATUS_SHIPPED   = 'shipped';
    const STATUS_DELIVERED = 'delivered';
    const STATUS_CANCELLED = 'cancelled';
    protected $fillable = [
        'user_id','status','subtotal','discount','shipping_cost','total',
        'shipping_address','shipping_service','shipping_service_name',
        'tracking_code','coupon_id','notes',
    ];
    protected $casts = [
        'subtotal'      => 'decimal:2',
        'discount'      => 'decimal:2',
        'shipping_cost' => 'decimal:2',
        'total'         => 'decimal:2',
        'shipping_address' => 'array',
    ];
    public function user(): BelongsTo   { return $this->belongsTo(User::class); }
    public function items(): HasMany    { return $this->hasMany(OrderItem::class); }
    public function payment(): HasOne   { return $this->hasOne(Payment::class); }
    public function coupon(): BelongsTo { return $this->belongsTo(Coupon::class); }
    public function isPaid(): bool { return $this->status === self::STATUS_PAID; }
    public function getStatusLabelAttribute(): string {
        return match($this->status) {
            self::STATUS_PENDING   => 'Aguardando pagamento',
            self::STATUS_PAID      => 'Pago',
            self::STATUS_SHIPPED   => 'Enviado',
            self::STATUS_DELIVERED => 'Entregue',
            self::STATUS_CANCELLED => 'Cancelado',
            default => $this->status,
        };
    }
}
