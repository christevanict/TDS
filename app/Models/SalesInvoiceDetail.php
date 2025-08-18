<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesInvoiceDetail extends Model
{
    use HasFactory;
    protected $table = 'sales_invoice_detail';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'sales_invoice_number',
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
        'company_code',
        'add_tax_detail',
        'department_code',
        'acc_number_cogs',
        'acc_number_sales',
        'acc_number_inventory',
        'created_by',
        'updated_by',
        'so_id',
        'description',
        'delivery_order_number',
        'sales_order_number',
        'warehouse_code',
    ];
    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_code');
    }
    public function units()
    {
        return $this->belongsTo(ItemUnit::class, 'unit', 'unit');
    }

    public function baseUnit(){
        return $this->belongsTo(ItemUnit::class,'base_unit','unit');
    }

    public function salesInvoice(){
        return $this->belongsTo(SalesInvoice::class,'sales_invoice_number','sales_invoice_number');
    }

    public function so()
    {
        return $this->belongsTo(SalesOrder::class,'so_id','id');
    }
}
