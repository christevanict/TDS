<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PbrDetail extends Model
{
    use HasFactory;
    protected $table = 'pbr_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'pbr_number',
        'item_id',
        'so_id',
        'qty',
        'unit',
        'price',
        'disc_percent',
        'disc_nominal',
        'disc_header',
        'nominal',
        'base_qty',
        'base_unit',
        'qty_left',
        'base_qty_left',
        'status',
        'description',
        'company_code',
        'department_code',
        'warehouse_code',
        'created_by',
        'updated_by',
    ];

    public function items()
    {
        return $this->belongsTo(Item::class,'item_id','item_code');
    }
    public function units()
    {
        return $this->belongsTo(ItemUnit::class, 'unit', 'unit');
    }

    public function baseUnit(){
        return $this->belongsTo(ItemUnit::class,'base_unit','unit');
    }

    public function so()
    {
        return $this->belongsTo(SalesOrderDetail::class,'so_id','id');
    }
}
