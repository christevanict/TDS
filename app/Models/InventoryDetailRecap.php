<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InventoryDetailRecap extends Model
{
    use HasFactory;
    protected $table = 'inventory_detail_recap';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'document_number',
        'document_date',
        'from_to',
        'transaction_type',
        'item_id',
        'quantity',
        'unit',
        'base_quantity',
        'unit_base',
        'department_code',
        'company_code',
        'first_qty',
        'last_qty',
        'created_by',
        'updated_by',
        'warehouse_id',
        'total',
        'cogs',
        'qty_actual'
    ];
}
