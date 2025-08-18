<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GoodReceiptDetail extends Model
{
    use HasFactory;
    protected $table = 'good_receipt_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'good_receipt_number',
        'item_id',
        'qty',
        'unit',
        'number_row',
        'base_qty',
        'base_unit',
        'department_code',
        'purchase_order_number',
        'description',
        'created_by',
        'updated_by',
    ];

    public function items(){
        return $this->belongsTo(Item::class,'item_id','item_code');
    }


    public function department(){
        return $this->belongsTo(Department::class,'department_code','department_code');
    }
    public function units(){
        return $this->belongsTo(ItemUnit::class,'unit','unit');
    }

    public function baseUnits(){
        return $this->belongsTo(ItemUnit::class,'base_unit','unit');
    }
}
