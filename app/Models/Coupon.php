<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class Coupon extends Model {
    protected $fillable = ['code','type','value','min_order_value','max_uses','used_count','is_active','expires_at'];
    protected $casts = ['value' => 'decimal:2','min_order_value' => 'decimal:2','is_active' => 'boolean','expires_at' => 'datetime'];
    public function isValid(): bool {
        if (!$this->is_active) return false;
        if ($this->expires_at && $this->expires_at->isPast()) return false;
        if ($this->max_uses && $this->used_count >= $this->max_uses) return false;
        return true;
    }
    public function calculateDiscount(float $subtotal): float {
        if ($this->min_order_value && $subtotal < $this->min_order_value) return 0;
        return $this->type === 'percent'
            ? round($subtotal * ($this->value / 100), 2)
            : min($this->value, $subtotal);
    }
}
