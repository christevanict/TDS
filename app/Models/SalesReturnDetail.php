<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesReturnDetail extends Model
{
    use HasFactory;
    protected $table = 'sales_return_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'sales_return_number',
        'item_id',
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
        'account_number',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
        'description',
    ];

    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }

    // Define relationship to Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }

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
}
