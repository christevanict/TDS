<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetPurchase extends Model
{
    use HasFactory;

    protected $table = 'asset_purchases';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'asset_purchase_number',
        'asset_number',
        'supplier_code',
        'document_date',
        'due_date',
        'subtotal',
        'add_tax',
        'nominal',
        'created_by',
        'updated_by',
    ];

    public function assetDetail()
    {
        return $this->belongsTo(AssetDetail::class, 'asset_number', 'asset_number');
    }

    public function depreciation()
    {
        return $this->belongsTo(Depreciation::class, 'depreciation_code', 'depreciation_code');
    }

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class,'supplier_code','supplier_code');
    }
}
