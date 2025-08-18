<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item extends Model
{
    use HasFactory;
    protected $table = 'item';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'item_code',
        'item_name',
        'item_category',
        'base_unit',
        'sales_unit',
        'purchase_unit',
        'additional_tax',
        'include',
        'company_code',
        'created_by',
        'updated_by',
        'warehouse_code',
        'department_code'
    ];

    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(ItemCategory::class, 'item_category', 'item_category_code');
    }

    public function baseUnits(): BelongsTo
    {
        return $this->belongsTo(ItemUnit::class, 'base_unit', 'unit');
    }

    public function salesUnit(): BelongsTo
    {
        return $this->belongsTo(ItemUnit::class, 'sales_unit', 'unit');
    }

    public function purchaseUnit(): BelongsTo
    {
        return $this->belongsTo(ItemUnit::class, 'purchase_unit', 'unit');
    }

    public function itemSalesPrices()
    {
        return $this->hasMany(ItemSalesPrice::class, 'item_code', 'item_code');
    }

    public function itemDetails()
    {
        return $this->hasMany(ItemDetail::class, 'item_code', 'item_code');
    }
    public function salesOrderDetails()
    {
        return $this->hasMany(SalesOrderDetail::class, 'item_id','item_code');
    }

    public function warehouses(){
        return $this->belongsTo(Warehouse::class,'warehouse_code','warehouse_code');
    }

}
