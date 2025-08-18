<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemDetail extends Model
{
    use HasFactory;

    protected $table = 'item_details';

    protected $primaryKey = 'id'; // Auto-increment primary key

    public $incrementing = true; // Ensure auto-increment

    public $timestamps = true; // Manage timestamps

    protected $fillable = [
        'item_code',
        'base_unit',
        'conversion',
        'unit_conversion',
        'barcode',
        'status',
        'department_code',
        'company_code',
        'created_by',
        'updated_by',
    ];

    /**
     * Relationship with the Item model.
     */
    public function item()
    {
        return $this->belongsTo(Item::class, 'item_code', 'item_code');
    }

    /**
     * Relationship with the ItemUnit model for base unit.
     */
    public function baseUnit()
    {
        return $this->belongsTo(ItemUnit::class, 'base_unit', 'unit');
    }

    /**
     * Relationship with the ItemUnit model for unit conversion.
     */
    public function unitConversion()
    {
        return $this->belongsTo(ItemUnit::class, 'unit_conversion', 'unit');
    }

    /**
     * Relationship with the Company model.
     */
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }
    public function inboundDetails() {
        return $this->hasMany(InboundDetail::class, 'item_code', 'item_code');
    }
}
