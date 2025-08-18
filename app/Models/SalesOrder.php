<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class SalesOrder extends Model
{
    use HasFactory;
    protected $table = 'sales_order';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'sales_order_number',
        'document_date',
        'eta_date',
        'etd_date',
        'destination',
        'hbl',
        'vessel',
        'shipper',
        'mbl',
        'loading',
        'shipment',
        'customer_code',
        'tax',
        'include',
        'reimburse',
        'status_reimburse',
        'subtotal',
        'disc_nominal',
        'tax_revenue',
        'add_tax',
        'total',
        'token',
        'is_nt',
        'company_code',
        'department_code',
        'notes',
        'status',
        'is_pbr',
        'cont',
        'con_qty',
        'con_invoice',
        'created_by',
        'updated_by',
        'due_date',
        'cancel_notes',
    ];
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code'); // assuming 'code' is the primary key in the Company model
    }
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code'); // assuming 'code' is the primary key in the Department model
    }
    public function details() {
        return $this->hasMany(SalesOrderDetail::class, 'sales_order_number', 'sales_order_number');
    }
    public function customers()
    {
        return $this->belongsTo(Customer::class, 'customer_code', 'customer_code');
    }
    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'shipper', 'supplier_code');
    }
    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }
    public function sis() {
        return $this->hasOne(SalesInvoice::class, 'sales_order_number', 'sales_order_number');
    }
    public function ris() {
        return $this->hasOne(Reimburse::class, 'contract_document_number', 'sales_order_number');
    }
    public function items()
    {
        return $this->hasMany(Item::class, 'id', 'id');
    }
    public function taxs()
    {
        return $this->belongsTo(TaxMaster::class, 'tax', 'tax_code');
    }
    public function itemDetails()
    {
        return $this->hasMany(ItemDetail::class, 'item_code', 'item_code');
    }
}
