<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class DeliveryOrderDetail extends Model
{
    use HasFactory;
    protected $table = 'delivery_order_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'delivery_order_number',
        'item_id',
        'qty',
        'unit',
        'number_row',
        'base_qty',
        'base_unit',
        'department_code',
        'sales_order_number',
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
    public function sos(){
        return $this->belongsTo(SalesOrder::class,'sales_order_number','sales_order_number');
    }

}
