<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PurchaseRequisitionDetail extends Model
{
    use HasFactory;

    protected $table = 'purchase_requisition_detail';
    protected $softDelete = false; // This should be set to false unless you're using soft deletes
    public $incrementing = true; // Indicates whether the IDs are auto-incrementing
    public $timestamps = true; // Indicates whether the model should use timestamps
    protected $primaryKey = 'id'; // Primary key of the table
    protected $fillable = [
        'purchase_requisition_number',
        'item_id',
        'unit',
        'qty',
        'qty_left',
        'base_unit',
        'base_qty',
        'notes',
        'department_code',
        'created_by',
        'updated_by',
        'status',
    ];


    // Define relationship to Department model
    public function department()
    {
        return $this->belongsTo(Department::class, 'department_code', 'department_code');
    }

    // Define relationship to PurchaseOrder model
    public function purchaseReq()
    {
        return $this->belongsTo(PurchaseRequisition::class, 'purchase_requisition_number', 'purchase_requisition_number');
    }

    // Define relationship to Item model
    public function items()
    {
        return $this->belongsTo(Item::class, 'item_id', 'item_code');
    }

    // Define relationship to ItemDetail model for base_unit
    public function itemDetail()
    {
        return $this->hasOne(ItemDetail::class, 'item_code', 'item_id'); // Use item_id here
    }

    public function units(){
        return $this->belongsTo(ItemUnit::class,'unit','unit');
    }
}
