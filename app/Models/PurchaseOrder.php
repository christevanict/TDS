<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseOrder extends Model
{
    use HasFactory;
    protected $table = 'purchase_order';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'purchase_order_number',
        'sales_order_number',
        'document_date',
        'delivery_date',
        'due_date',
        'supplier_code',
        'currency_code',
        'tax',
        'status',
        'include',
        'subtotal',
        'disc_percent',
        'disc_nominal',
        'tax_revenue',
        'tax_revenue_tariff',
        'add_tax',
        'total',
        'token',
        'notes',
        'company_code',
        'department_code',
        'created_by',
        'updated_by',
        'purchase_requisition_number',
        'cancel_notes',
    ];

    public function suppliers()
    {
        return $this->belongsTo(Supplier::class, 'supplier_code', 'supplier_code');
    }

    // Define the relationship with the SalesOrder model
    public function salesOrder()
    {
        return $this->belongsTo(SalesOrder::class, 'sales_order_number', 'sales_order_number');
    }

    // Define the relationship with the Company model
    public function company()
    {
        return $this->belongsTo(Company::class, 'company_code', 'company_code');
    }

    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }

    // Define the relationship with the Currency model if applicable
    public function currency()
    {
        return $this->belongsTo(Currency::class, 'currency_code', 'currency_code');
    }

    // You can also define a relationship for purchase order details if applicable
    public function details()
    {
        return $this->hasMany(PurchaseOrderDetail::class, 'purchase_order_number', 'purchase_order_number');
    }

    public function taxs()
    {
        return $this->belongsTo(TaxMaster::class, 'tax', 'tax_code');
    }

    public function users()
    {
        return $this->belongsTo(Users::class, 'created_by', 'username'); // assuming 'code' is the primary key in the Company model
    }
}
