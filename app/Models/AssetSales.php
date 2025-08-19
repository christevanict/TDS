<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AssetSales extends Model
{
    use HasFactory;

    protected $table = 'asset_sales';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';

    protected $fillable = [
        'asset_sales_number',
        'asset_number',
        'customer_code',
        'document_date',
        'due_date',
        'subtotal',
        'add_tax',
        'nominal',
        'accum_value',
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

    public function customers()
    {
        return $this->belongsTo(Customer::class,'customer_code','customer_code');
    }
}
