<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoldOrder extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hold_orders';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'reference_id',
        'cart_items',
        'total_amount',
        'note',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'cart_items' => 'array', // Automatically cast JSON data to array
        'total_amount' => 'decimal:2',
    ];

    /**
     * Scope a query to filter by reference ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param string $referenceId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByReferenceId($query, $referenceId)
    {
        return $query->where('reference_id', $referenceId);
    }
    public function details()
    {
        return $this->hasMany(HoldOrderDetail::class, 'hold_order_id');
    }
}
