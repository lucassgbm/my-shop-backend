<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
class Address extends Model {
    protected $fillable = ['user_id','label','name','phone','zipcode','street','number','complement','neighborhood','city','state','country'];
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function getFullAddressAttribute(): string {
        return "{$this->street}, {$this->number}" . ($this->complement ? ", {$this->complement}" : "") . " — {$this->neighborhood}, {$this->city}/{$this->state}";
    }
}
