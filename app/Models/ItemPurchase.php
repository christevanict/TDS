<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ItemPurchase extends Model
{
    use HasFactory;
    protected $table = 'item_purchase';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'barcode',
        'item_code',
        'purchase_price',
        'unit',
        'supplier',
        'department_code',
        'company_code',
        'created_by',
        'updated_by',
    ];
    public function company(){
        return $this->belongsTo(Company::class,'company_code','company_code');
    }
    public function barcodes(){
        return $this->belongsTo(ItemDetail::class,'barcode','barcode');
    }
    public function items(){
        return $this->belongsTo(Item::class,'item_code','item_code');
    }
    public function suppliers(){
        return $this->belongsTo(Supplier::class,'supplier','supplier_code');
    }
    public function units(){
        return $this->belongsTo(ItemDetail::class,'unit','unit_conversion');
    }
    public function unitn(){
        return $this->belongsTo(ItemUnit::class,'unit','unit');
    }
    public function itemDetails(){
        return $this->hasMany(ItemDetail::class,'item_code','item_code');
    }
}
