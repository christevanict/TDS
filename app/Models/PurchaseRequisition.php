<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisition extends Model
{
    use HasFactory;
    protected $table = 'purchase_requisition';
    protected $softDelete = false;
    public $incrementing = true;
    public $timestamps = true;
    protected $primaryKey = 'id';
    protected $fillable = [
        'purchase_requisition_number',
        'document_date',
        'ordered_date',
        'department_code',
        'supplier_code',
        'total',
        'notes',
        'created_by',
        'updated_by',
        'status',
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

    // Define the relationship with the Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }


    // You can also define a relationship for purchase order details if applicable
    public function details()
    {
        return $this->hasMany(PurchaseRequisitionDetail::class, 'purchase_requisition_number', 'purchase_requisition_number');
    }

    
}
