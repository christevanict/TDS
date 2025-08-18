<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoldOrderDetail extends Model
{
    use HasFactory;

    /**
     * The table associated with the model.
     *
     * @var string
     */
    protected $table = 'hold_orders_detail';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'hold_order_id',
        'item',
        'price',
        'quantity',
        'total',
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'price' => 'decimal:2', // Automatically cast price to decimal with 2 decimal places
        'total' => 'decimal:2', // Automatically cast total to decimal with 2 decimal places
    ];

    /**
     * Get the hold order associated with the detail.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function holdOrder()
    {
        return $this->belongsTo(HoldOrder::class, 'hold_order_id');
    }

    /**
     * Scope a query to filter by hold order ID.
     *
     * @param \Illuminate\Database\Eloquent\Builder $query
     * @param int $holdOrderId
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function scopeByHoldOrderId($query, $holdOrderId)
    {
        return $query->where('hold_order_id', $holdOrderId);
    }
}
