<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrderDetail extends Model
{
    use HasFactory;
    protected $table = 'sales_order_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'sales_order_number',
        'item_id',
        'qty',
        'unit',
        'price',
        'disc_percent',
        'disc_nominal',
        'nominal',
        'number_row',
        'base_qty',
        'base_unit',
        'cancel',
        'status',
        'description',
        'qty_left',
        'base_qty_left',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
    ];

    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_code');
    }

    public function units()
    {
        return $this->belongsTo(ItemUnit::class, 'unit', 'unit');
    }
    public function baseUnit()
    {
        return $this->belongsTo(ItemUnit::class, 'base_unit', 'unit');
    }

    public function so()
    {
        return $this->belongsTo(SalesOrder::class,'sales_order_number','sales_order_number');
    }
}
